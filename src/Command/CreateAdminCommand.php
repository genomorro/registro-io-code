<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a default super admin user if none exists.',
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

        $userRepo = $this->entityManager->getRepository(User::class);
        $admin = $userRepo->findOneBy(['username' => 'admin']);

        if (!$admin) {
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setName('Administrador');
            $admin->setRoles(['ROLE_SUPER_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $io->success('Super Admin user created successfully with username "admin" and password "admin123"!');
        } else {
            $io->warning('Super Admin user "admin" already exists.');
        }

        return Command::SUCCESS;
    }
}
