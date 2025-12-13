<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

#[AsCommand(
    name: 'app:import-data:appointment',
    description: 'Imports appointment data from an external database.',
)]
class ImportAppointmentDataCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Imports appointment data from an external database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Importing Appointment Data');

        $externalDbUrl = $_ENV['EXTERNAL_DB_URL'] ?? null;

        if (!$externalDbUrl) {
            $io->error('The EXTERNAL_DB_URL environment variable is not set.');
            return Command::FAILURE;
        }

        try {
            $externalConnection = DriverManager::getConnection(['url' => $externalDbUrl]);
            $externalData = $externalConnection->fetchAllAssociative('SELECT * FROM citasmedicas');
        } catch (\Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        $this->entityManager->getConnection()->executeStatement('DELETE FROM appointment');

        $metadata = $this->entityManager->getClassMetadata(Appointment::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        foreach ($externalData as $row) {
            $patient = $this->entityManager->getRepository(Patient::class)->find($row['idPac']);
            if (!$patient) {
                $io->warning(sprintf('Patient with ID %d not found, skipping appointment %d.', $row['idPac'], $row['idCita']));
                continue;
            }

            $appointment = new Appointment();
            $appointment->setPatient($patient);
            $appointment->setAgenda(ucwords(strtolower($row['lugRealizacion'])));
            $appointment->setSpecialty(ucfirst(strtolower($row['especialidad'])));
            $appointment->setLocation(ucfirst(strtolower($row['ubicacion'])));
            $appointment->setDateAt(new \DateTimeImmutable($row['fechaCita']));
            $appointment->setType(ucfirst(strtolower($row['tipoConsulta'])));
            $appointment->setStatus($row['estatusCita']);
            
            $this->entityManager->persist($appointment);

            $id = $row['idCita'];
            $metadata->setIdentifierValues($appointment, ['id' => $id]);
        }

        $this->entityManager->flush();

        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

        $maxId = 0;
        foreach ($externalData as $row) {
            if ($row['idCita'] > $maxId) {
                $maxId = $row['idCita'];
            }
        }

        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform()->getName();

        if ($platform === 'sqlite') {
            $connection->executeStatement("UPDATE sqlite_sequence SET seq = $maxId WHERE name = 'appointment'");
        } elseif ($platform === 'mysql') {
            $connection->executeStatement("ALTER TABLE appointment AUTO_INCREMENT = " . ($maxId + 1));
        }

        $io->success('Appointment data imported successfully!');

        return Command::SUCCESS;
    }
}
