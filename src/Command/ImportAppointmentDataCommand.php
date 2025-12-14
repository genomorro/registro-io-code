<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Repository\PatientRepository;
use App\Service\ConnectionService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
name: 'app:import-data:appointment',
description: 'Imports appointment data from an external database.',
)]
class ImportAppointmentDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private PatientRepository $patientRepository;
    private ConnectionService $connectionService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PatientRepository $patientRepository,
        ConnectionService $connectionService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->patientRepository = $patientRepository;
        $this->connectionService = $connectionService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $conn = $this->connectionService->getConnection();
            $sql = 'SELECT * FROM citamedica';
            $stmt = $conn->executeQuery($sql);
            $appointmentData = $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();
        
        try {
            $this->entityManager->getConnection()->executeStatement('DELETE FROM appointment');

            $metadata = $this->entityManager->getClassMetaData(Appointment::class);
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

            $maxId = 0;
            foreach ($appointmentData as $data) {
                $patient = $this->patientRepository->find($data['idPac']);

                if ($patient) {
                    $appointment = new Appointment();
                    $this->entityManager->persist($appointment);

                    $metadata->getReflectionProperty('id')->setValue($appointment, $data['idCita']);

                    $appointment->setPatient($patient);
                    $appointment->setAgenda(ucwords(strtolower($data['lugRealizacion'])));
                    $appointment->setSpecialty(ucfirst(strtolower($data['especialidad'])));
                    $appointment->setLocation(ucfirst(strtolower($data['ubicacion'])));
                    $appointment->setDateAt(new \DateTimeImmutable($data['fechaCita']));
                    $appointment->setType(ucfirst(strtolower($data['tipoConsulta'])));
                    $appointment->setStatus($data['estatusCita']);

                    if ($data['idCita'] > $maxId) {
                        $maxId = $data['idCita'];
                    }
                }
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "appointment"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE appointment AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Appointment data imported successfully.');

        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $io->error('An error occurred during data import: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
