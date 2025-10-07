<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Patient;
use App\Form\PatientType;
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
    public function index(PatientRepository $patientRepository): Response
    {
        return $this->render('patient/index.html.twig', [
            'patients' => $patientRepository->findWithAppointmentsAndAttendanceToday(),
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
    public function show(Patient $patient, VisitorRepository $visitorRepository): Response
    {
        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
            'todays_visitors' => $visitorRepository->findTodaysVisitorsByPatient($patient),
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
        $attendance = new Attendance();
        $attendance->setPatient($patient);
        $attendance->setCheckInAt(new \DateTimeImmutable());

        $entityManager->persist($attendance);
        $entityManager->flush();

        return $this->redirectToRoute('app_patient_index');
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
