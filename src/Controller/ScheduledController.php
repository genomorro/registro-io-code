<?php

namespace App\Controller;

use App\Entity\Scheduled;
use App\Form\ScheduledImportType;
use App\Form\ScheduledType;
use App\Repository\ScheduledRepository;
use Doctrine\ORM\EntityManagerInterface;
use HugoSEIGLE\SymfonyImportExportBundle\Services\Import\ImporterInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    #[Route('/import', name: 'app_scheduled_import', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        ImporterInterface $importer,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $session = $request->getSession();

        if ($request->isMethod('POST')) {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('file');

            if (!$file) {
                $this->addFlash('danger', $translator->trans('File is required.'));
                return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
            }

            try {
                $result = $importer->import($file, Scheduled::class, ScheduledImportType::class);

                if ($result->isValid()) {
                    $preview = [];
                    $processedLabels = [];
                    $hasExistingRecords = false;

                    $allEntities = array_merge($result->getCreatedEntities(), $result->getUpdatedEntities());

                    foreach ($allEntities as $entity) {
                        /** @var Scheduled $entity */
                        $label = $entity->getLabel();

                        // Deduplication: skip duplicate rows with the same label in the file
                        if (in_array($label, $processedLabels, true)) {
                            continue;
                        }
                        $processedLabels[] = $label;

                        $existingScheduled = $entityManager->getRepository(Scheduled::class)->findOneBy(['label' => $label]);
                        $action = $existingScheduled ? 'update' : 'create';
                        if ($existingScheduled) {
                            $hasExistingRecords = true;
                        }

                        $preview[] = [
                            'action' => $action,
                            'area_id' => $entity->getArea()?->getId(),
                            'area_name' => $entity->getArea() ? (string) $entity->getArea() : '',
                            'label' => $label,
                            'name' => $entity->getName(),
                            'institution' => $entity->getInstitution(),
                            'subject' => $entity->getSubject(),
                            'beginAt' => $entity->getBeginAt()?->format('Y-m-d'),
                            'endAt' => $entity->getEndAt()?->format('Y-m-d'),
                        ];
                    }

                    $session->set('scheduled_import_preview', [
                        'items' => $preview,
                        'has_existing' => $hasExistingRecords,
                    ]);
                    $session->remove('scheduled_import_errors');
                } else {
                    $errors = [];
                    foreach ($result->getErrors() as $error) {
                        $errors[] = [
                            'row' => $error->row,
                            'field' => $error->field,
                            'message' => $error->message,
                            'value' => is_array($error->value) ? implode(', ', $error->value) : (string) $error->value,
                        ];
                    }

                    $session->set('scheduled_import_errors', $errors);
                    $session->remove('scheduled_import_preview');
                }
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
        }

        $previewData = $session->get('scheduled_import_preview');
        $errors = $session->get('scheduled_import_errors');

        return $this->render('scheduled/import.html.twig', [
            'preview' => $previewData['items'] ?? null,
            'has_existing' => $previewData['has_existing'] ?? false,
            'errors' => $errors,
        ]);
    }

    #[Route('/import/confirm', name: 'app_scheduled_import_confirm', methods: ['POST'])]
    public function importConfirm(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $session = $request->getSession();
        $previewData = $session->get('scheduled_import_preview');

        if (!$previewData || !is_array($previewData) || empty($previewData['items'])) {
            $this->addFlash('danger', $translator->trans('No preview data found to import.'));
            return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
        }

        $updateExisting = $request->request->getBoolean('update_existing', false);
        $items = $previewData['items'];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($items as $item) {
            $scheduled = $entityManager->getRepository(Scheduled::class)->findOneBy(['label' => $item['label']]);
            if (!$scheduled) {
                $scheduled = new Scheduled();
                $createdCount++;
            } else {
                if (!$updateExisting) {
                    continue; // Skip updating existing records if user chose not to update
                }
                $updatedCount++;
            }

            $area = $entityManager->getRepository(\App\Entity\Area::class)->find($item['area_id']);

            $scheduled->setArea($area);
            $scheduled->setLabel($item['label']);
            $scheduled->setName($item['name']);
            $scheduled->setInstitution($item['institution']);
            $scheduled->setSubject($item['subject']);
            $scheduled->setBeginAt(new \DateTimeImmutable($item['beginAt']));
            $scheduled->setEndAt(new \DateTimeImmutable($item['endAt']));

            $entityManager->persist($scheduled);
        }

        $entityManager->flush();
        $session->remove('scheduled_import_preview');

        $this->addFlash('success', $translator->trans(
            'Import completed successfully. Created: %created%, Updated: %updated%',
            ['%created%' => $createdCount, '%updated%' => $updatedCount]
        ));

        return $this->redirectToRoute('app_scheduled_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import/cancel', name: 'app_scheduled_import_cancel', methods: ['POST'])]
    public function importCancel(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $session = $request->getSession();
        $session->remove('scheduled_import_preview');
        $session->remove('scheduled_import_errors');

        return $this->redirectToRoute('app_scheduled_import', [], Response::HTTP_SEE_OTHER);
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
