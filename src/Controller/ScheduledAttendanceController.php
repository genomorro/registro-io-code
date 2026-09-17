<?php

namespace App\Controller;

use App\Entity\ScheduledAttendance;
use App\Form\ScheduledAttendanceType;
use App\Repository\ScheduledAttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/scheduled/attendance')]
final class ScheduledAttendanceController extends AbstractController
{
    #[Route(name: 'app_scheduled_attendance_index', methods: ['GET', 'POST'])]
    public function index(ScheduledAttendanceRepository $scheduledAttendanceRepository, PaginatorInterface $paginator, Request $request): Response
    {
	$filter = $request->query->get('filter');
	$query = $scheduledAttendanceRepository->paginateScheduledAttendance($filter);

	$scheduled_attendances = $paginator->paginate(
	    $query,
	    $request->query->getInt('page', 1),
	    10
	);

        return $this->render('scheduled_attendance/index.html.twig', [
            'scheduled_attendances' => $scheduled_attendances,
        ]);
    }

    #[Route('/new', name: 'app_scheduled_attendance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $scheduledAttendance = new ScheduledAttendance();
	$flash = $translator->trans('Attendance added successfully');
        $form = $this->createForm(ScheduledAttendanceType::class, $scheduledAttendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

	    $scheduledAttendance->setCheckInUser($this->getUser());
	    
            $entityManager->persist($scheduledAttendance);
            $entityManager->flush();

	    $this->addFlash('success', $flash);
            return $this->redirectToRoute('app_scheduled_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled_attendance/new.html.twig', [
            'scheduled_attendance' => $scheduledAttendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scheduled_attendance_show', methods: ['GET'])]
    public function show(ScheduledAttendance $scheduledAttendance): Response
    {
        return $this->render('scheduled_attendance/show.html.twig', [
            'scheduled_attendance' => $scheduledAttendance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_scheduled_attendance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ScheduledAttendance $scheduledAttendance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScheduledAttendanceType::class, $scheduledAttendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_scheduled_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scheduled_attendance/edit.html.twig', [
            'scheduled_attendance' => $scheduledAttendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_scheduled_attendance_delete', methods: ['POST'])]
    public function delete(Request $request, ScheduledAttendance $scheduledAttendance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$scheduledAttendance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($scheduledAttendance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_scheduled_attendance_index', [], Response::HTTP_SEE_OTHER);
    }
}
