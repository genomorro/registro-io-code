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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/patient')]
final class PatientController extends AbstractController
{
    #[Route(name: 'app_patient_index', methods: ['GET'])]
    public function index(
        PatientRepository $patientRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $filter = $request->query->get('filter');
        $query = $patientRepository->paginatePatient($filter);

        $patients = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            ['distinct' => false]
        );

        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/new', name: 'app_patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

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
        AttendanceRepository $attendanceRepository,
        Request $request,
        VisitorRepository $visitorRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $todaysAttendance = $attendanceRepository->findLatestByPatientAndDate($patient, new \DateTime());

        $todaysAppointments = $appointmentRepository->createTodaysAppointmentsByPatientQueryBuilder($patient)
						    ->getQuery()
						    ->getResult();
        $otherAppointments = $appointmentRepository->createOtherAppointmentsByPatientQueryBuilder($patient)
						   ->getQuery()
						   ->getResult();
        $todaysVisitors = $visitorRepository->createTodaysVisitorsByPatientQueryBuilder($patient)
					    ->getQuery()
					    ->getResult();

        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
            'todays_attendance' => $todaysAttendance,
            'todays_appointments' => $todaysAppointments,
            'other_appointments' => $otherAppointments,
            'todays_visitors' => $todaysVisitors,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_patient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

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
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($patient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-in', name: 'app_patient_check_in', methods: ['POST'])]
    public function checkIn(Request $request, Patient $patient, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $tag = $request->request->get('tag');
	$flash = $translator->trans('A numeric tag is required to check in.');
        $redirectRoute = $request->request->get('redirect_route', 'app_patient_index');
        $redirectParams = $request->request->all('redirect_params');

        if (!is_numeric($tag)) {
            $this->addFlash('danger', $flash);

            return $this->redirectToRoute($redirectRoute, $redirectParams);
        }

        $attendance = new Attendance();
        $attendance->setPatient($patient);
        $attendance->setCheckInAt(new \DateTimeImmutable());
	$attendance->setCheckInUser($this->getUser());
        $attendance->setTag((int) $tag);
	
        $entityManager->persist($attendance);
        $entityManager->flush();

	$evidenceData = $request->request->get('evidence');
        if ($evidenceData) {
            $data = explode(',', $evidenceData);
            $imageData = base64_decode($data[1]);

            $checkInAt = $attendance->getCheckInAt();
            $year = $checkInAt->format('Y');
            $month = $checkInAt->format('m');

            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/attendance/' . $year . '/' . $month;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = $attendance->getId() . '-' . $checkInAt->format('YmdHis') . '.png';
            $filepath = $uploadDir . '/' . $filename;

	    file_put_contents($filepath, $imageData);

	    $attendance->setEvidence('/uploads/attendance/' . $year . '/' . $month . '/' . $filename);
            $entityManager->flush();
        }

        return $this->redirectToRoute($redirectRoute, $redirectParams);
    }

    #[Route('/{id}/check-out', name: 'app_patient_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Patient $patient, AttendanceRepository $attendanceRepository, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $attendance = $attendanceRepository->findLatestByPatientAndDate($patient, new \DateTime());
        if ($attendance) {
            $attendance->setCheckOutAt(new \DateTimeImmutable());
	    $attendance->setCheckOutUser($this->getUser());
            $entityManager->flush();
        }

        $redirectRoute = $request->request->get('redirect_route', 'app_patient_index');
        $redirectParams = $request->request->all('redirect_params');

        return $this->redirectToRoute($redirectRoute, $redirectParams);
    }
}
