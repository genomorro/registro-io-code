<?php

namespace App\Controller;

use App\Entity\Hospitalized;
use App\Form\HospitalizedType;
use App\Repository\HospitalizedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hospitalized')]
final class HospitalizedController extends AbstractController
{
    #[Route(name: 'app_hospitalized_index', methods: ['GET'])]
    public function index(
        HospitalizedRepository $hospitalizedRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $filter = $request->query->get('filter');
        $query = $hospitalizedRepository->paginateHospitalized($filter);

        $hospitalizeds = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('hospitalized/index.html.twig', [
            'hospitalizeds' => $hospitalizeds,
        ]);
    }

    #[Route('/new', name: 'app_hospitalized_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
	
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

    #[Route('/{id}', name: 'app_hospitalized_show', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(Hospitalized $hospitalized): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');
	
        return $this->render('hospitalized/show.html.twig', [
            'hospitalized' => $hospitalized,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hospitalized_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Hospitalized $hospitalized, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
	
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

    #[Route('/{id}', name: 'app_hospitalized_delete', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(Request $request, Hospitalized $hospitalized, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
	
        if ($this->isCsrfTokenValid('delete'.$hospitalized->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hospitalized);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hospitalized_index', [], Response::HTTP_SEE_OTHER);
    }
}
