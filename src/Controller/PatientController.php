<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Patient;
use App\Form\PatientType;
use App\Repository\AppointmentRepository;
use App\Repository\AttendanceRepository;
use App\Repository\PatientRepository;
use App\Repository\VisitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/patient')]
final class PatientController extends AbstractController
{
    #[Route(name: 'app_patient_index', methods: ['GET'])]
    public function index(PatientRepository $patientRepository, Request $request): Response
    {
        $queryBuilder = $patientRepository->findWithAppointmentsAndAttendanceTodayQueryBuilder();

        $adapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($queryBuilder);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('patient/index.html.twig', [
            'patients' => $pagerfanta,
        ]);
    }

    #[Route('/new', name: 'app_patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('patient/new.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_patient_show', methods: ['GET'])]
    public function show(
	Patient $patient,
	AppointmentRepository $appointmentRepository,
	Request $request,
	VisitorRepository $visitorRepository
    ): Response {
        $todaysAppointmentsQB = $appointmentRepository->createTodaysAppointmentsByPatientQueryBuilder($patient);
        $otherAppointmentsQB = $appointmentRepository->createOtherAppointmentsByPatientQueryBuilder($patient);
        $todaysVisitorsQB = $visitorRepository->createTodaysVisitorsByPatientQueryBuilder($patient);
	
        $todaysAppointmentsAdapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($todaysAppointmentsQB);
        $todaysAppointments = new \Pagerfanta\Pagerfanta($todaysAppointmentsAdapter);
        $todaysAppointments->setMaxPerPage(20);
        $todaysAppointments->setCurrentPage($request->query->getInt('page_today_appointments', 1));
	
        $otherAppointmentsAdapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($otherAppointmentsQB);
        $otherAppointments = new \Pagerfanta\Pagerfanta($otherAppointmentsAdapter);
        $otherAppointments->setMaxPerPage(20);
        $otherAppointments->setCurrentPage($request->query->getInt('page_other_appointments', 1));

        $todaysVisitorsAdapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($todaysVisitorsQB);
        $todaysVisitors = new \Pagerfanta\Pagerfanta($todaysVisitorsAdapter);
        $todaysVisitors->setMaxPerPage(20);
        $todaysVisitors->setCurrentPage($request->query->getInt('page_today_visitors', 1));
	
        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
            'todays_appointments' => $todaysAppointments,
            'other_appointments' => $otherAppointments,
            'todays_visitors' => $todaysVisitors,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_patient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('patient/edit.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_patient_delete', methods: ['POST'])]
    public function delete(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($patient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-in', name: 'app_patient_check_in', methods: ['POST'])]
    public function checkIn(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $tag = $request->request->get('tag');

        if (!is_numeric($tag)) {
            $this->addFlash('danger', 'A numeric tag is required to check in.');

            return $this->redirectToRoute('app_patient_index');
        }

        $attendance = new Attendance();
        $attendance->setPatient($patient);
        $attendance->setCheckInAt(new \DateTimeImmutable());
        $attendance->setTag((int) $tag);

        $entityManager->persist($attendance);
        $entityManager->flush();

        return $this->redirectToRoute('app_patient_index');
    }

    #[Route('/{id}/check-in-show', name: 'app_patient_check_in_show', methods: ['POST'])]
    public function checkInShow(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $tag = $request->request->get('tag');

        if (!is_numeric($tag)) {
            $this->addFlash('danger', 'A numeric tag is required to check in.');

            return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
        }

        $attendance = new Attendance();
        $attendance->setPatient($patient);
        $attendance->setCheckInAt(new \DateTimeImmutable());
        $attendance->setTag((int) $tag);

        $entityManager->persist($attendance);
        $entityManager->flush();

        return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
    }

    #[Route('/{id}/check-out', name: 'app_patient_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Patient $patient, AttendanceRepository $attendanceRepository, EntityManagerInterface $entityManager): Response
    {
        $attendance = $attendanceRepository->findOneByPatientAndDate($patient, new \DateTime());
        if ($attendance) {
            $attendance->setCheckOutAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patient_index');
    }

    #[Route('/{id}/check-out-show', name: 'app_patient_check_out_show', methods: ['POST'])]
    public function CheckOutShow(Request $request, Patient $patient, AttendanceRepository $attendanceRepository, EntityManagerInterface $entityManager): Response
    {
        $attendance = $attendanceRepository->findOneByPatientAndDate($patient, new \DateTime());

        if ($attendance) {
            $attendance->setCheckOutAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
    }
}
