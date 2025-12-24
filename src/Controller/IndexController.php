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

    #[Route('/php', name: 'app_php')]
    public function php(): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        ob_start();
        phpinfo();
        $info = ob_get_clean();

        return new Response($info);
    }

    #[Route('/about', name: 'app_about')]
    public function about (): Response
    {
        $creator = 'Edgar Uriel Domínguez Espinoza';
        $year = '2025';

        $process = new Process(['php', '../bin/console', 'about']);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $symfony_about = $process->getOutput();

        return $this->render('about.html.twig', [
            'creator' => $creator,
            'year' => $year,
            'symfony_about' => $symfony_about,
        ]);
    }

    public function redirectToLocale(): RedirectResponse
    {
        return $this->redirectToRoute('app_index', ['_locale' => 'es']);
    }
}
