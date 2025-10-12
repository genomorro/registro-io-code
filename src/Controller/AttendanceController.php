<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Form\AttendanceType;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/attendance')]
final class AttendanceController extends AbstractController
{
    #[Route(name: 'app_attendance_index', methods: ['GET'])]
    public function index(AttendanceRepository $attendanceRepository, Request $request): Response
    {
	$queryBuilder = $attendanceRepository->findAllWithPatient();

        $adapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($queryBuilder);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('attendance/index.html.twig', [
            'attendances' => $pagerfanta,
        ]);
    }

    #[Route('/new', name: 'app_attendance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $attendance = new Attendance();
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attendance);
            $entityManager->flush();

            return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('attendance/new.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attendance_show', methods: ['GET'])]
    public function show(Attendance $attendance): Response
    {
        return $this->render('attendance/show.html.twig', [
            'attendance' => $attendance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_attendance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('attendance/edit.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attendance_delete', methods: ['POST'])]
    public function delete(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attendance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($attendance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/check-out-show', name: 'app_attendance_check_out_show', methods: ['POST'])]
    public function checkOutShow(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $attendance->setCheckOutAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->redirectToRoute('app_attendance_show', ['id' => $attendance->getId()]);
    }


    #[Route('/{id}/check-out', name: 'app_attendance_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $attendance->setCheckOutAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->redirectToRoute('app_attendance_index');
    }
}
