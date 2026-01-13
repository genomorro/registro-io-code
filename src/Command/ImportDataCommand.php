<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-data',
    description: 'Imports data from remote databases.',
)]
class ImportDataCommand extends Command
{
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

        $arguments = [];
        if ($update) {
            $arguments['--update'] = true;
        }

        $patientCommand = $this->getApplication()->find('app:import-data:patient');
        $patientCommand->run(new ArrayInput($arguments), $output);

        $hospitalizedCommand = $this->getApplication()->find('app:import-data:hospitalized');
        $hospitalizedCommand->run(new ArrayInput($arguments), $output);

        $appointmentCommand = $this->getApplication()->find('app:import-data:appointment');
        $appointmentCommand->run(new ArrayInput($arguments), $output);

        $io->success('All data imported successfully.');

        return Command::SUCCESS;
    }
}
