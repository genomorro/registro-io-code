<?php

namespace App\Command;

use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\DriverManager;

#[AsCommand(
    name: 'app:import-data:patient',
    description: 'Imports patient data from a remote MySQL database',
)]
class ImportPatientDataCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connectionParams = [
            'dbname' => 'r3sp1ra770ri4x8025',
            'user' => 'pbaconnectmsql',
            'password' => 'Tduc$aupydl$5t',
            'host' => '192.168.27.30',
            'driver' => 'pdo_mysql',
        ];
        
        $conn = DriverManager::getConnection($connectionParams);

        $sql = 'SELECT * FROM pacientes';
        $stmt = $conn->executeQuery($sql);
        $patientsData = $stmt->fetchAllAssociative();

        $this->entityManager->getConnection()->beginTransaction();
        
        try {
            $this->entityManager->getConnection()->executeStatement('DELETE FROM patient');

            $maxId = 0;
            foreach ($patientsData as $patientData) {
                if ($patientData['idPac'] < 4) {
                    continue;
                }

                $patient = new Patient();
                $this->entityManager->persist($patient);

                $metadata = $this->entityManager->getClassMetaData(Patient::class);
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $metadata->getReflectionProperty('id')->setValue($patient, $patientData['idPac']);

                $patient->setFile($patientData['numExpediente']);
                
                $name = ($patientData['nomPaciente'] ?? '') . ' ' . ($patientData['primerApellido'] ?? '') . ' ' . ($patientData['segundoApellido'] ?? '');
                $patient->setName(str_replace(',', '', $name));

                $disability = !in_array($patientData['tipoDificultad'], ['NINGUNA', 'SE IGNORA']);
                $patient->setDisability($disability);

                if ($patientData['idPac'] > $maxId) {
                    $maxId = $patientData['idPac'];
                }
            }

            $this->entityManager->flush();

            $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "patient"', [$maxId]);

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
