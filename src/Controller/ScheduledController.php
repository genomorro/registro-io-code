<?php

namespace App\Controller;

use App\Entity\Scheduled;
use App\Form\ScheduledType;
use App\Repository\ScheduledRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
