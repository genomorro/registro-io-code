<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about (): Response
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        $phpinfo = str_replace(
            '<table',
            '<table class="table table-striped table-bordered table-sm"',
            $phpinfo
        );

        $creator = 'Edgar Uriel Domínguez Espinoza';
        $year = "2025-".date("Y");

        $process = new Process(['php', '../bin/console', 'about']);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $symfony_about = $process->getOutput();

        return $this->render('about.html.twig', [
	    'phpinfo' => $phpinfo,
	    'version' => $_ENV['VERSION'],
            'creator' => $creator,
            'symfony_about' => $symfony_about,
            'year' => $year,
        ]);
    }

    public function redirectToLocale(): RedirectResponse
    {
        return $this->redirectToRoute('app_index', ['_locale' => 'es']);
    }
}
