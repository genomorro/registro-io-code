<?php

namespace App\Controller;

use App\Entity\Visitor;
use App\Form\VisitorType;
use App\Repository\VisitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/visitor')]
final class VisitorController extends AbstractController
{
    #[Route(name: 'app_visitor_index', methods: ['GET'])]
    public function index(VisitorRepository $visitorRepository, Request $request): Response
    {
	$queryBuilder = $visitorRepository->createQueryBuilder('v');

	$adapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($queryBuilder);
	$pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
	$pagerfanta->setMaxPerPage(20);
	$pagerfanta->setCurrentPage($request->query->getInt('page', 1));
	
        return $this->render('visitor/index.html.twig', [
            'visitors' => $pagerfanta,
        ]);
    }

    #[Route('/new', name: 'app_visitor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $visitor = new Visitor();
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visitor->setCheckInAt(new \DateTimeImmutable());
            $entityManager->persist($visitor);
            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visitor/new.html.twig', [
            'visitor' => $visitor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visitor_show', methods: ['GET'])]
    public function show(Visitor $visitor): Response
    {
        return $this->render('visitor/show.html.twig', [
            'visitor' => $visitor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visitor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visitor/edit.html.twig', [
            'visitor' => $visitor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visitor_delete', methods: ['POST'])]
    public function delete(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visitor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visitor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-out', name: 'app_visitor_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('checkout'.$visitor->getId(), $request->getPayload()->getString('_token'))) {
            $visitor->setCheckOutAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visitor_index');
    }

    #[Route('/{id}/check-out-show', name: 'app_visitor_check_out_show', methods: ['POST'])]
    public function checkOutShow(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('checkout'.$visitor->getId(), $request->getPayload()->getString('_token'))) {
            $visitor->setCheckOutAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visitor_show', ['id' => $visitor->getId()]);
    }
}
