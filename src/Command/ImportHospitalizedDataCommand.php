<?php

namespace App\Command;

use App\Entity\Hospitalized;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\DriverManager;

#[AsCommand(
    name: 'app:import-data:hospitalized',
    description: 'Imports hospitalized data from a remote MySQL database',
)]
class ImportHospitalizedDataCommand extends Command
{
    private $entityManager;
    private $patientRepository;

    public function __construct(EntityManagerInterface $entityManager, PatientRepository $patientRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->patientRepository = $patientRepository;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connectionParams = [
            'dbname' => getenv('REMOTE_DB_NAME'),
            'user' => getenv('REMOTE_DB_USER'),
            'password' => getenv('REMOTE_DB_PASSWORD'),
            'host' => getenv('REMOTE_DB_HOST'),
            'driver' => 'pdo_mysql',
        ];
        
        try {
            $conn = DriverManager::getConnection($connectionParams);
            $sql = 'SELECT * FROM pacienteshospitalizados';
            $stmt = $conn->executeQuery($sql);
            $hospitalizedData = $stmt->fetchAllAssociative();
        } catch (\Exception $e) {
            $io->error('Could not connect to the remote database: ' . $e->getMessage());
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
