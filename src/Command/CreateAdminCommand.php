<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Area;
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a default super admin user (admin / admin123).',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userRepository = $this->entityManager->getRepository(User::class);
        $existing = $userRepository->findOneBy(['username' => 'admin']);

        if (!$existing) {
            $user = new User();
            $user->setUsername('admin');
            $user->setName('Super Admin');
            $user->setRoles(['ROLE_SUPER_ADMIN']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));

            $this->entityManager->persist($user);
        } else {
            $io->info('Admin user already exists.');
        }

        // Seed Area if none exists
        $areaRepo = $this->entityManager->getRepository(Area::class);
        $area = $areaRepo->findOneBy(['building' => 'Building A']);
        if (!$area) {
            $area = new Area();
            $area->setBuilding('Building A');
            $area->setUnit('Unit 101');
            $area->setExtension(1234);
            $this->entityManager->persist($area);
        }

        // Seed Employee if none exists
        $employeeRepo = $this->entityManager->getRepository(Employee::class);
        $employee = $employeeRepo->findOneBy(['name' => 'Dr. John Smith']);
        if (!$employee) {
            $employee = new Employee();
            $employee->setName('Dr. John Smith');
            $employee->setNumber(5555);
            $employee->setArea($area);
            $this->entityManager->persist($employee);
        }

        $this->entityManager->flush();

        $io->success('Admin user (admin / admin123) and sample Area/Employee successfully created!');

        return Command::SUCCESS;
    }
}
