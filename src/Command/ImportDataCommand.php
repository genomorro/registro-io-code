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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $patientCommand = $this->getApplication()->find('app:import-data:patient');
        $patientCommand->run(new ArrayInput([]), $output);

        $hospitalizedCommand = $this->getApplication()->find('app:import-data:hospitalized');
        $hospitalizedCommand->run(new ArrayInput([]), $output);

        $appointmentCommand = $this->getApplication()->find('app:import-data:appointment');
        $appointmentCommand->run(new ArrayInput([]), $output);

        $io->success('All data imported successfully.');

        return Command::SUCCESS;
    }
}
