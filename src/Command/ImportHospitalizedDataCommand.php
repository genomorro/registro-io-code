<?php

namespace App\Command;

use App\Entity\Hospitalized;
use App\Repository\HospitalizedRepository;
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
    name: 'app:import-data:hospitalized',
    description: 'Imports hospitalized data from a remote MySQL database.',
)]
class ImportHospitalizedDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private PatientRepository $patientRepository;
    private ConnectionService $connectionService;
    private HospitalizedRepository $hospitalizedRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PatientRepository $patientRepository,
        ConnectionService $connectionService,
        HospitalizedRepository $hospitalizedRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->patientRepository = $patientRepository;
        $this->connectionService = $connectionService;
        $this->hospitalizedRepository = $hospitalizedRepository;
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
            $sql = 'SELECT * FROM pacientesHospitalizados ORDER BY idHospital';
            $stmt = $conn->executeQuery($sql);
            $hospitalizedData = $stmt->iterateAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $localHospitalized = $this->hospitalizedRepository->findAll();
            $localHospitalizedMap = [];
            foreach ($localHospitalized as $hospitalized) {
                $localHospitalizedMap[$hospitalized->getId()] = $hospitalized;
            }

            $maxId = 0;
            $processedIds = [];

            // Disable SQL logger to prevent memory leaks
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

            foreach ($hospitalizedData as $data) {
                if ($data['idHospital'] < 1) {
                    continue;
                }

                if (in_array($data['idHospital'], $processedIds)) {
                    $io->warning(sprintf('Duplicate hospitalized ID %d found in source data, skipping.', $data['idHospital']));
                    continue;
                }
                $processedIds[] = $data['idHospital'];

                $patient = $this->patientRepository->find($data['idPaciente']);
                if (!$patient) {
                    $io->warning(sprintf('Patient with ID %d not found for hospitalized ID %d, skipping.', $data['idPaciente'], $data['idHospital']));
                    continue;
                }

                if (isset($localHospitalizedMap[$data['idHospital']])) {
                    // Update existing hospitalized
                    $hospitalized = $localHospitalizedMap[$data['idHospital']];
                    unset($localHospitalizedMap[$data['idHospital']]);
                } else {
                    // Create new hospitalized
                    $hospitalized = new Hospitalized();
                    $metadata = $this->entityManager->getClassMetaData(Hospitalized::class);
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                    $metadata->getReflectionProperty('id')->setValue($hospitalized, $data['idHospital']);
                    $this->entityManager->persist($hospitalized);
                }

                $hospitalized->setService($data['servicioHosp']);
                $hospitalized->setBed($data['camaHosp']);
                $hospitalized->setPatient($patient);

                if ($data['idHospital'] > $maxId) {
                    $maxId = $data['idHospital'];
                }
            }

            // Remove hospitalized that are no longer in the remote database
            foreach ($localHospitalizedMap as $hospitalizedToRemove) {
                $this->entityManager->remove($hospitalizedToRemove);
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "hospitalized"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE hospitalized AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Hospitalized data updated successfully.');
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
            $sql = 'SELECT * FROM pacientesHospitalizados';
            $stmt = $conn->executeQuery($sql);
            $hospitalizedData = $stmt->iterateAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->entityManager->getConnection()->executeStatement('DELETE FROM hospitalized');

            $metadata = $this->entityManager->getClassMetaData(Hospitalized::class);
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

            $maxId = 0;
            foreach ($hospitalizedData as $data) {
                $patient = $this->patientRepository->find($data['idPaciente']);

                if ($patient) {
                    $hospitalized = new Hospitalized();
                    $this->entityManager->persist($hospitalized);

                    $metadata->getReflectionProperty('id')->setValue($hospitalized, $data['idHospital']);

                    $hospitalized->setService($data['servicioHosp']);
                    $hospitalized->setBed($data['camaHosp']);
                    $hospitalized->setPatient($patient);

                    if ($data['idHospital'] > $maxId) {
                        $maxId = $data['idHospital'];
                    }
                }
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "hospitalized"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE hospitalized AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Hospitalized data imported successfully.');
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $io->error('An error occurred during data import: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
