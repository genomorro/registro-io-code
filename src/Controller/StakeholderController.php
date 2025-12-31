<?php

namespace App\Controller;

use App\Entity\Stakeholder;
use App\Form\StakeholderType;
use App\Repository\StakeholderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stakeholder')]
final class StakeholderController extends AbstractController
{
    #[Route(name: 'app_stakeholder_index', methods: ['GET'])]
    public function index(StakeholderRepository $stakeholderRepository): Response
    {
        return $this->render('stakeholder/index.html.twig', [
            'stakeholders' => $stakeholderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stakeholder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stakeholder = new Stakeholder();
        $form = $this->createForm(StakeholderType::class, $stakeholder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stakeholder);
            $entityManager->flush();

            return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stakeholder/new.html.twig', [
            'stakeholder' => $stakeholder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stakeholder_show', methods: ['GET'])]
    public function show(Stakeholder $stakeholder): Response
    {
        return $this->render('stakeholder/show.html.twig', [
            'stakeholder' => $stakeholder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stakeholder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stakeholder $stakeholder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StakeholderType::class, $stakeholder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stakeholder/edit.html.twig', [
            'stakeholder' => $stakeholder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stakeholder_delete', methods: ['POST'])]
    public function delete(Request $request, Stakeholder $stakeholder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stakeholder->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stakeholder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
    }
}
