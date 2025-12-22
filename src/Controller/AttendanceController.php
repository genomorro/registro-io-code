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
    #[Route(name: 'app_attendance_index', methods: ['GET', 'POST'])]
    public function index(AttendanceRepository $attendanceRepository, Request $request): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

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
	$this->denyAccessUnlessGranted('ROLE_USER');

        $attendance = new Attendance();
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

	    $attendance->setCheckInUser($this->getUser());

	    $entityManager->persist($attendance);
            $entityManager->flush();

	    $evidenceData = $form->get('evidence')->getData();
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
	$this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('attendance/show.html.twig', [
            'attendance' => $attendance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_attendance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

	$originalCheckOutAt = $attendance->getCheckOutAt();
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
	    $newCheckOutAt = $attendance->getCheckOutAt();
	    if ($originalCheckOutAt === null && $newCheckOutAt !== null) {
		$attendance->setCheckOutUser($this->getUser());
	    }

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
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$attendance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($attendance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-out/{redirectRoute}', name: 'app_attendance_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Attendance $attendance, EntityManagerInterface $entityManager, string $redirectRoute): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isCsrfTokenValid('checkout'.$attendance->getId(), $request->getPayload()->getString('_token'))) {
            $attendance->setCheckOutAt(new \DateTimeImmutable());
	    $attendance->setCheckOutUser($this->getUser());
            $entityManager->flush();
        }

        $routeParameters = [];
        if ($redirectRoute === 'app_attendance_show') {
            $routeParameters['id'] = $attendance->getId();
        }

        return $this->redirectToRoute($redirectRoute, $routeParameters);
    }
}
