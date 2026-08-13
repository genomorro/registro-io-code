<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/report')]
class ReportController extends AbstractController
{
    #[Route(path: '/', name: 'app_report_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $metabaseSecretKey = "e49c45b18a09b030b159b7c3b8727e9b6b4c2556b732d340545ba2c81c12e720";

        $payload = [
            'resource' => ['dashboard' => 2],
            'params' => new \stdClass(), // Ensure empty params object, i.e. {}
            'exp' => time() + (10 * 60), // 10 minute expiration
            '_embedding_params' => new \stdClass() // Match Metabase token requirements
        ];

        try {
            $token = JWT::encode($payload, $metabaseSecretKey, 'HS256');
        } catch (\Exception $e) {
            $token = '';
        }

        return $this->render('report/index.html.twig', [
            'metabaseToken' => $token,
        ]);
    }
}
