<?php

namespace App\Controller;

use App\Entity\Date;
use App\Form\DateType;
use App\Repository\DateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/date')]
final class DateController extends AbstractController
{
    #[Route(name: 'app_date_index', methods: ['GET'])]
    public function index(DateRepository $dateRepository): Response
    {
        return $this->render('date/index.html.twig', [
            'dates' => $dateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_date_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $date = new Date();
        $form = $this->createForm(DateType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($date);
            $entityManager->flush();

            return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('date/new.html.twig', [
            'date' => $date,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_date_show', methods: ['GET'])]
    public function show(Date $date): Response
    {
        return $this->render('date/show.html.twig', [
            'date' => $date,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_date_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Date $date, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DateType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('date/edit.html.twig', [
            'date' => $date,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_date_delete', methods: ['POST'])]
    public function delete(Request $request, Date $date, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$date->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($date);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
    }
}
