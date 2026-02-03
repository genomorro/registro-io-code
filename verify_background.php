<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use App\Entity\Stakeholder;
use App\Entity\User;

require __DIR__.'/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$wrapper = $container->get(DompdfWrapperInterface::class);

$user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
$stakeholder = $entityManager->getRepository(Stakeholder::class)->findOneBy(['name' => 'John Doe']);

if (!$user || !$stakeholder) {
    die("Test data not found. Run previous setup scripts if needed.\n");
}

// Since I'm testing the logic in the controller, I'll just check if it renders fine.
// But I can't easily call the controller from here without a client.
// I'll just check if the file_exists check in the controller would work.

$projectDir = $kernel->getProjectDir();
$backgroundPath = $projectDir . '/assets/images/fondo-2026.jpeg';
echo "Checking background path: $backgroundPath\n";
if (file_exists($backgroundPath)) {
    echo "Background image exists.\n";
} else {
    echo "Background image NOT found.\n";
}
