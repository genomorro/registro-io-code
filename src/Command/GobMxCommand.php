<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
name: 'app:gob-mx',
description: 'Downloads and modifies gobmx.js to avoid conflicts.',
)]
class GobMxCommand extends Command
{
    private HttpClientInterface $httpClient;
    private string $projectDir;

    public function __construct(HttpClientInterface $httpClient, string $projectDir)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $targetDir = $this->projectDir . '/assets/vendor/gobmx';
        $targetFile = $targetDir . '/gobmx.js';

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $io->error("Failed to create directory: $targetDir");
                return Command::FAILURE;
            }
        }

        $urls = [
            'https://framework-gb.cdn.gob.mx/gm/v3/assets/js/gobmx.js',
            'https://web.archive.org/web/20250305104858id_/https://framework-gb.cdn.gob.mx/gm/v3/assets/js/gobmx.js',
        ];

        $requestOptions = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'Accept' => '*/*',
                'Referer' => 'https://www.gob.mx/',
            ],
            'max_redirects' => 5,
        ];

        $content = null;
        $downloadedUrl = null;

        foreach ($urls as $url) {
            $io->info("Attempting to download gobmx.js from $url");
            try {
                $response = $this->httpClient->request('GET', $url, $requestOptions);
                if ($response->getStatusCode() === 200) {
                    $content = $response->getContent();
                    $downloadedUrl = $url;
                    break;
                }
                $io->warning("Received HTTP " . $response->getStatusCode() . " from $url");
            } catch (\Exception $e) {
                $io->warning("Failed to download gobmx.js from $url: " . $e->getMessage());
            }
        }

        if ($content === null) {
            $io->error("Failed to download gobmx.js from all configured sources.");
            return Command::FAILURE;
        }

        $io->info("Successfully downloaded gobmx.js from $downloadedUrl");

        // Modify gobmx.js to not load Bootstrap
        $io->info("Modifying gobmx.js to disable Bootstrap loading...");
        
        // Use a regex to target specifically the bootstrap loading block
        $pattern = '/\/\/ Carga de bootstrap.*?appendChild\(allScripts\);/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '/* Bootstrap loading disabled by app:gob-mx */', $content);
        } else {
            $io->warning("Bootstrap loading block not found in gobmx.js. It might have changed or is already modified.");
        }

        if (file_put_contents($targetFile, $content) === false) {
            $io->error("Failed to write to $targetFile");
            return Command::FAILURE;
        }

        $io->success("gobmx.js has been downloaded, modified, and saved to $targetFile");

        return Command::SUCCESS;
    }
}
