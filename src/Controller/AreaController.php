<?php

namespace App\Controller;

use App\Entity\Area;
use App\Form\AreaType;
use App\Repository\AreaRepository;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/area')]
final class AreaController extends AbstractController
{
    #[Route(name: 'app_area_index', methods: ['GET'])]
    public function index(AreaRepository $areaRepository, PaginatorInterface $paginator, Request $request): Response
    {
	$filter = $request->query->get('filter');
        $query = $areaRepository->paginateArea($filter);

	$areas = $paginator->paginate(
	    $query,
	    $request->query->getInt('page', 1),
	    10
	);
	
        return $this->render('area/index.html.twig', [
            'areas' => $areas,
        ]);
    }

    #[Route('/new', name: 'app_area_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $area = new Area();
	$flash = $translator->trans('Area added successfully.');
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($area);
            $entityManager->flush();

	    $this->addFlash('success', $flash);
            return $this->redirectToRoute('app_area_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('area/new.html.twig', [
            'area' => $area,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_area_show', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(Area $area, EmployeeRepository $employeeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $filter = $request->query->get('filter');
        $query = $employeeRepository->paginateEmployeesByArea($area, $filter);

        $employees = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('area/show.html.twig', [
            'area' => $area,
            'employees' => $employees,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_area_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Area $area, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(AreaType::class, $area);
	$flash = $translator->trans('Area updated successfully.');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

	    $this->addFlash('primary', $flash);
            return $this->redirectToRoute('app_area_index', ['id' => $area->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('area/edit.html.twig', [
            'area' => $area,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_area_delete', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(Request $request, Area $area, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
	$flash = $translator->trans('Area deleted successfully.');
        if ($this->isCsrfTokenValid('delete'.$area->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($area);
            $entityManager->flush();
        }

	$this->addFlash('danger', $flash);
        return $this->redirectToRoute('app_area_index', [], Response::HTTP_SEE_OTHER);
    }
}
