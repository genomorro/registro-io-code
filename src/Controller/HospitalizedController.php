<?php

namespace App\Controller;

use App\Entity\Hospitalized;
use App\Form\HospitalizedType;
use App\Repository\HospitalizedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hospitalized')]
final class HospitalizedController extends AbstractController
{
    #[Route(name: 'app_hospitalized_index', methods: ['GET'])]
    public function index(HospitalizedRepository $hospitalizedRepository): Response
    {
        return $this->render('hospitalized/index.html.twig', [
            'hospitalizeds' => $hospitalizedRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hospitalized_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hospitalized = new Hospitalized();
        $form = $this->createForm(HospitalizedType::class, $hospitalized);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hospitalized);
            $entityManager->flush();

            return $this->redirectToRoute('app_hospitalized_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hospitalized/new.html.twig', [
            'hospitalized' => $hospitalized,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hospitalized_show', methods: ['GET'])]
    public function show(Hospitalized $hospitalized): Response
    {
        return $this->render('hospitalized/show.html.twig', [
            'hospitalized' => $hospitalized,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hospitalized_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hospitalized $hospitalized, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HospitalizedType::class, $hospitalized);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hospitalized_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hospitalized/edit.html.twig', [
            'hospitalized' => $hospitalized,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hospitalized_delete', methods: ['POST'])]
    public function delete(Request $request, Hospitalized $hospitalized, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hospitalized->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hospitalized);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hospitalized_index', [], Response::HTTP_SEE_OTHER);
    }
}
