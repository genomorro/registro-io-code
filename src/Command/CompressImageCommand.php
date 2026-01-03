<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:compress-image',
    description: 'Compresses images in the uploads directory.',
)]
class CompressImageCommand extends Command
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uploadsPath = $this->projectDir . '/public/uploads';
        $entityTypes = ['visitor', 'attendance', 'stakeholder'];
        $currentDate = new \DateTime();

        foreach ($entityTypes as $type) {
            $path = $uploadsPath . '/' . $type;
            if (!is_dir($path)) {
                continue;
            }

            $yearFinder = new Finder();
            $yearFinder->directories()->in($path)->depth('== 0');

            foreach ($yearFinder as $yearDir) {
                $year = (int)$yearDir->getRelativePathname();
                
                // Find both directories (months) and tar.gz archives
                $itemFinder = new Finder();
                $itemFinder->in($yearDir->getRealPath())->depth('== 0')->name('/(\d{2}|\d{2}\.tar\.gz)$/');

                foreach ($itemFinder as $item) {
                    if ($item->isDir()) {
                        $month = (int)$item->getRelativePathname();
                        $dirDate = new \DateTime("$year-$month-01");
                        $interval = $currentDate->diff($dirDate);
                        $months = $interval->y * 12 + $interval->m;

                        if ($months >= 24) {
                            $this->archiveMonth($item, $io);
                        } elseif ($months >= 12) {
                            $this->compressImagesInMonth($item, $io);
                        }
                    } elseif ($item->isFile()) {
                        $month = (int)basename($item->getFilename(), '.tar.gz');
                        $fileDate = new \DateTime("$year-$month-01");
                        $interval = $currentDate->diff($fileDate);
                        $months = $interval->y * 12 + $interval->m;

                        if ($months >= 36) {
                            $this->deleteMonthArchive($item, $io);
                        }
                    }
                }

                if (count(scandir($yearDir->getRealPath())) == 2) {
                    $io->text("Deleting empty year directory: " . $yearDir->getRealPath());
                    rmdir($yearDir->getRealPath());
                }
            }
        }

        $io->success('Image compression and cleanup complete.');
        return Command::SUCCESS;
    }

    private function compressImagesInMonth(\SplFileInfo $monthDir, SymfonyStyle $io)
    {
        $finder = new Finder();
        $finder->files()->in($monthDir->getRealPath())->name('*.png');

        foreach ($finder as $file) {
            $originalPath = $file->getRealPath();
            $compressedPath = $originalPath . '.gz';
            
            $io->text("Compressing image: $originalPath");
            $gz = gzopen($compressedPath, 'w9');
            gzwrite($gz, file_get_contents($originalPath));
            gzclose($gz);

            unlink($originalPath);
        }
    }

    private function archiveMonth(\SplFileInfo $monthDir, SymfonyStyle $io)
    {
        $monthPath = $monthDir->getRealPath();
        $parentDir = dirname($monthPath);
        $monthName = $monthDir->getBasename();
        $archivePath = $parentDir . '/' . $monthName . '.tar';

        $io->text("Archiving month: $monthPath");
        $tar = new \PharData($archivePath);
        $tar->buildFromDirectory($monthPath);
        $tar->compress(\Phar::GZ);

        if (file_exists($archivePath)) {
            unlink($archivePath); // remove the .tar file
        }

        // Remove the original directory
        $this->removeDirectory($monthPath);
    }

    private function deleteMonthArchive(\SplFileInfo $archiveFile, SymfonyStyle $io)
    {
        $archivePath = $archiveFile->getRealPath();
        $io->text("Deleting old archive: $archivePath");
        unlink($archivePath);
    }

    private function removeDirectory(string $path)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($path);
    }
}
