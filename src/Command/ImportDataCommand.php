<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
name: 'app:import-data',
description: 'Imports data from remote databases.',
)]
class ImportDataCommand extends Command
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $lockFilePath = $this->projectDir . '/var/maintenance.lock';

        touch($lockFilePath);

        try {
            $patientCommand = $this->getApplication()->find('app:import-data:patient');
            $patientCommand->run(new ArrayInput(['--no-maintenance' => true]), $output);

            $hospitalizedCommand = $this->getApplication()->find('app:import-data:hospitalized');
            $hospitalizedCommand->run(new ArrayInput(['--no-maintenance' => true]), $output);

            $appointmentCommand = $this->getApplication()->find('app:import-data:appointment');
            $appointmentCommand->run(new ArrayInput(['--no-maintenance' => true]), $output);

            $io->success('All data imported successfully.');

            return Command::SUCCESS;
        } finally {
            if (file_exists($lockFilePath)) {
                unlink($lockFilePath);
            }
        }
    }
}
