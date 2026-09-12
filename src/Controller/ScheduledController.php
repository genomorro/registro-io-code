<?php

namespace App\Controller;

use App\Entity\Scheduled;
use App\Form\ScheduledImportType;
use App\Form\ScheduledType;
use App\Repository\ScheduledRepository;
use App\Service\ScheduledImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/scheduled')]
final class ScheduledController extends AbstractController
{
    #[Route(name: 'app_scheduled_index', methods: ['GET'])]
    public function index(ScheduledRepository $scheduledRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

	$filter = $request->query->get('filter');
	$query = $scheduledRepository->paginateScheduled($filter);

	$scheduleds = $paginator->paginate(
	    $query,
	    $request->query->getInt('page', 1),
	    10
	);
	
        return $this->render('scheduled/index.html.twig', [
            'scheduleds' => $scheduleds,
        ]);
    }

    #[Route('/import', name: 'app_scheduled_import', methods: ['GET', 'POST'])]
    public function import(Request $request, ScheduledImporterService $importerService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ScheduledImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if ($file) {
                $tempFilename = uniqid('import_', true) . '.' . $file->getClientOriginalExtension();
                $tempPath = sys_get_temp_dir() . '/' . $tempFilename;
                $file->move(sys_get_temp_dir(), $tempFilename);

                $originalFilename = $file->getClientOriginalName();
                $analysis = $importerService->analyzeFile($tempPath, $originalFilename);

                // Store analysis data and tempPath in session
                $session = $request->getSession();
                $session->set('scheduled_import_analysis', $analysis);
                $session->set('scheduled_import_temppath', $tempPath);

                return $this->redirectToRoute('app_scheduled_import_preview', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('scheduled/import.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/import/preview', name: 'app_scheduled_import_preview', methods: ['GET'])]
    public function importPreview(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $session = $request->getSession();
        $analysis = $session->get('scheduled_import_analysis');

        if (!$analysis) {
            $this->addFlash('danger', 'No hay datos de importación para previsualizar.');
            return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled/preview.html.twig', [
            'analysis' => $analysis,
        ]);
    }

    #[Route('/import/process', name: 'app_scheduled_import_process', methods: ['POST'])]
    public function importProcess(Request $request, ScheduledImporterService $importerService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $session = $request->getSession();
        $analysis = $session->get('scheduled_import_analysis');
        $tempPath = $session->get('scheduled_import_temppath');

        if (!$analysis || empty($analysis['rows'])) {
            $this->addFlash('danger', 'No hay datos de importación para procesar.');
            return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
        }

        $existingAction = $request->request->get('existing_action', 'skip');

        $result = $importerService->executeImport($analysis['rows'], $existingAction);

        // Store result in session for display and possible error download
        $session->set('scheduled_import_result', $result);

        // Clean up temp file
        if ($tempPath && file_exists($tempPath)) {
            @unlink($tempPath);
        }
        $session->remove('scheduled_import_temppath');
        $session->remove('scheduled_import_analysis');

        return $this->redirectToRoute('app_scheduled_import_result', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import/result', name: 'app_scheduled_import_result', methods: ['GET'])]
    public function importResult(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $session = $request->getSession();
        $result = $session->get('scheduled_import_result');

        if (!$result) {
            $this->addFlash('danger', 'No hay resultado de importación disponible.');
            return $this->redirectToRoute('app_scheduled_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled/result.html.twig', [
            'result' => $result,
        ]);
    }

    #[Route('/import/download-errors', name: 'app_scheduled_import_download_errors', methods: ['GET'])]
    public function downloadErrors(Request $request, ScheduledImporterService $importerService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $session = $request->getSession();
        $result = $session->get('scheduled_import_result');

        if (!$result || empty($result['unprocessed_rows'])) {
            $this->addFlash('warning', 'No hay registros omitidos o con error para descargar.');
            return $this->redirectToRoute('app_scheduled_index');
        }

        $csvContent = $importerService->generateErrorCsv($result['unprocessed_rows']);

        $response = new Response($csvContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'registros_omitidos_errores.csv'
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/new', name: 'app_scheduled_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $scheduled = new Scheduled();
	$flash = $translator->trans('Scheduled added successfully.');
        $form = $this->createForm(ScheduledType::class, $scheduled);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($scheduled);
            $entityManager->flush();

	    $this->addFlash('success', $flash);
            return $this->redirectToRoute('app_scheduled_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled/new.html.twig', [
            'scheduled' => $scheduled,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scheduled_show', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(Scheduled $scheduled): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('scheduled/show.html.twig', [
            'scheduled' => $scheduled,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_scheduled_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Scheduled $scheduled, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ScheduledType::class, $scheduled);
	$flash = $translator->trans('Scheduled updated successfully.');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

	    $this->addFlash('primary', $flash);
            return $this->redirectToRoute('app_scheduled_index', ['id' => $scheduled->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled/edit.html.twig', [
            'scheduled' => $scheduled,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scheduled_delete', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(Request $request, Scheduled $scheduled, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

	$flash = $translator->trans('Scheduled deleted successfully.');
        if ($this->isCsrfTokenValid('delete'.$scheduled->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($scheduled);
            $entityManager->flush();
	    $this->addFlash('danger', $flash);
        }

        return $this->redirectToRoute('app_scheduled_index', [], Response::HTTP_SEE_OTHER);
    }
}
