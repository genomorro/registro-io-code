<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
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
    name: 'app:import-data:appointment',
    description: 'Imports appointment data from a remote MySQL database.',
)]
class ImportAppointmentDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private PatientRepository $patientRepository;
    private ConnectionService $connectionService;
    private AppointmentRepository $appointmentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PatientRepository $patientRepository,
        ConnectionService $connectionService,
        AppointmentRepository $appointmentRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->patientRepository = $patientRepository;
        $this->connectionService = $connectionService;
        $this->appointmentRepository = $appointmentRepository;
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
            $sql = 'SELECT * FROM citasMedicas ORDER BY idCita';
            $stmt = $conn->executeQuery($sql);
            $appointmentData = $stmt->iterateAssociative();
        } catch (Exception $e) {
            $io->error('Could not connect to the external database: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $localAppointments = $this->appointmentRepository->findAll();
            $localAppointmentsMap = [];
            foreach ($localAppointments as $appointment) {
                $localAppointmentsMap[$appointment->getId()] = $appointment;
            }

            $maxId = 0;
            $processedIds = [];

            // Disable SQL logger to prevent memory leaks
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

            foreach ($appointmentData as $data) {
                if ($data['idCita'] < 1) {
                    continue;
                }

                if (in_array($data['idCita'], $processedIds)) {
                    $io->warning(sprintf('Duplicate appointment ID %d found in source data, skipping.', $data['idCita']));
                    continue;
                }
                $processedIds[] = $data['idCita'];

                $patient = $this->patientRepository->find($data['idPac']);
                if (!$patient) {
                    $io->warning(sprintf('Patient with ID %d not found for appointment ID %d, skipping.', $data['idPac'], $data['idCita']));
                    continue;
                }

                if (isset($localAppointmentsMap[$data['idCita']])) {
                    // Update existing appointment
                    $appointment = $localAppointmentsMap[$data['idCita']];
                    unset($localAppointmentsMap[$data['idCita']]);
                } else {
                    // Create new appointment
                    $appointment = new Appointment();
                    $metadata = $this->entityManager->getClassMetaData(Appointment::class);
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                    $metadata->getReflectionProperty('id')->setValue($appointment, $data['idCita']);
                    $this->entityManager->persist($appointment);
                }

                $appointment->setPatient($patient);
                $appointment->setAgenda(mb_convert_case($data['lugRealizacion'], MB_CASE_TITLE, 'UTF-8'));
                $appointment->setSpecialty(mb_convert_case($data['especialidad'], MB_CASE_TITLE, 'UTF-8'));
                $appointment->setLocation(mb_convert_case($data['ubicacion'], MB_CASE_TITLE, 'UTF-8'));
                $appointment->setDateAt(new \DateTimeImmutable($data['fechaCita']));
                $appointment->setType(mb_convert_case($data['tipoConsulta'], MB_CASE_TITLE, 'UTF-8'));
                $appointment->setStatus($data['estatusCita']);

                if ($data['idCita'] > $maxId) {
                    $maxId = $data['idCita'];
                }
            }

            // Remove appointments that are no longer in the remote database
            foreach ($localAppointmentsMap as $appointmentToRemove) {
                $this->entityManager->remove($appointmentToRemove);
            }

            $this->entityManager->flush();

            $platform = $this->entityManager->getConnection()->getDatabasePlatform()->getName();
            if ($platform === 'sqlite') {
                $this->entityManager->getConnection()->executeStatement('UPDATE sqlite_sequence SET seq = ? WHERE name = "appointment"', [$maxId]);
            } elseif ($platform === 'mysql') {
                $this->entityManager->getConnection()->executeStatement('ALTER TABLE appointment AUTO_INCREMENT = ?', [$maxId + 1]);
            }

            $this->entityManager->getConnection()->commit();

            $io->success('Appointment data updated successfully.');
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
            $sql = 'SELECT * FROM citasMedicas';
            $stmt = $conn->executeQuery($sql);
            $appointmentData = $stmt->iterateAssociative();
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
                    $appointment->setAgenda(mb_convert_case($data['lugRealizacion'], MB_CASE_TITLE, 'UTF-8'));
                    $appointment->setSpecialty(mb_convert_case($data['especialidad'], MB_CASE_TITLE, 'UTF-8'));
                    $appointment->setLocation(mb_convert_case($data['ubicacion'], MB_CASE_TITLE, 'UTF-8'));
                    $appointment->setDateAt(new \DateTimeImmutable($data['fechaCita']));
                    $appointment->setType(mb_convert_case($data['tipoConsulta'], MB_CASE_TITLE, 'UTF-8'));
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
