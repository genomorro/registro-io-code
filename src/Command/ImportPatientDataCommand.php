<?php

namespace App\Command;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use App\Service\ConnectionService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-data:patient',
    description: 'Imports patient data from a remote MySQL database.',
)]
class ImportPatientDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ConnectionService $connectionService;
    private PatientRepository $patientRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConnectionService $connectionService,
        PatientRepository $patientRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->connectionService = $connectionService;
        $this->patientRepository = $patientRepository;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'Update existing records instead of truncating the table.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $update = $input->getOption('update');

        if ($update) {
            return $this->executeUpdate($io);
        } else {
            return $this->executeTruncate($io);
        }
    }

    private function executeUpdate(SymfonyStyle $io): int
    {
        try {
            $conn = $this->connectionService->getConnection();
            $sql = 'SELECT * FROM Pacientes ORDER BY idPac';
            $stmt = $conn->executeQuery($sql);
            $patientsData = $stmt->iterateAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $localPatients = $this->patientRepository->findAll();
            $localPatientsMap = [];
            foreach ($localPatients as $patient) {
                $localPatientsMap[$patient->getId()] = $patient;
            }

            $maxId = 0;
            $processedIds = [];

            // Disable SQL logger to prevent memory leaks
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

            foreach ($patientsData as $patientData) {
                if ($patientData['idPac'] < 1) {
                    continue;
                }

                if (in_array($patientData['idPac'], $processedIds)) {
                    $io->warning(sprintf('Duplicate patient ID %d found in source data, skipping.', $patientData['idPac']));
                    continue;
                }
                $processedIds[] = $patientData['idPac'];

                if (isset($localPatientsMap[$patientData['idPac']])) {
                    // Update existing patient
                    $patient = $localPatientsMap[$patientData['idPac']];
                    unset($localPatientsMap[$patientData['idPac']]);
                } else {
                    // Create new patient
                    $patient = new Patient();
                    $metadata = $this->entityManager->getClassMetaData(Patient::class);
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                    $metadata->getReflectionProperty('id')->setValue($patient, $patientData['idPac']);
                    $this->entityManager->persist($patient);
                }

                if (empty($patientData['numExpediente'])) {
                    $io->warning(sprintf('Patient with ID %d has an empty file number (numExpediente), skipping.', $patientData['idPac']));
                    $this->entityManager->detach($patient);
                    continue;
                }
                $patient->setFile($patientData['numExpediente']);

                $name = ($patientData['nomPaciente'] ?? '') . ' ' . ($patientData['primerApellido'] ?? '') . ' ' . ($patientData['segundoApellido'] ?? '');
                $patient->setName(ucwords(str_replace(',', '', $name)));

                $disability = !in_array($patientData['tipoDificultad'], ['NINGUNA', 'SE IGNORA']);
                $patient->setDisability($disability);

                if ($patientData['idPac'] > $maxId) {
                    $maxId = $patientData['idPac'];
                }
            }

            // Remove patients that are no longer in the remote database
            foreach ($localPatientsMap as $patientToRemove) {
                $this->entityManager->remove($patientToRemove);
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "patient"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE patient AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Patient data updated successfully.');
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $io->error('An error occurred during data update: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function executeTruncate(SymfonyStyle $io): int
    {
        try {
            $conn = $this->connectionService->getConnection();
            $sql = 'SELECT * FROM pacientes';
            $stmt = $conn->executeQuery($sql);
            $patientsData = $stmt->iterateAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->entityManager->getConnection()->executeStatement('DELETE FROM patient');

            $maxId = 0;
            $processedIds = [];
            foreach ($patientsData as $patientData) {
                if ($patientData['idPac'] < 1) {
                    continue;
                }

                if (in_array($patientData['idPac'], $processedIds)) {
                    $io->warning(sprintf('Duplicate patient ID %d found in source data, skipping.', $patientData['idPac']));
                    continue;
                }
                $processedIds[] = $patientData['idPac'];

                $patient = new Patient();
                $this->entityManager->persist($patient);

                $metadata = $this->entityManager->getClassMetaData(Patient::class);
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $metadata->getReflectionProperty('id')->setValue($patient, $patientData['idPac']);

                if (empty($patientData['numExpediente'])) {
                    $io->warning(sprintf('Patient with ID %d has an empty file number (numExpediente), skipping.', $patientData['idPac']));
                    $this->entityManager->detach($patient);
                    continue;
                }
                $patient->setFile($patientData['numExpediente']);

                $name = ($patientData['nomPaciente'] ?? '') . ' ' . ($patientData['primerApellido'] ?? '') . ' ' . ($patientData['segundoApellido'] ?? '');
                $patient->setName(ucwords(str_replace(',', '', $name)));

                $disability = !in_array($patientData['tipoDificultad'], ['NINGUNA', 'SE IGNORA']);
                $patient->setDisability($disability);

                if ($patientData['idPac'] > $maxId) {
                    $maxId = $patientData['idPac'];
                }
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "patient"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE patient AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Patient data imported successfully.');
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $io->error('An error occurred during data import: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
