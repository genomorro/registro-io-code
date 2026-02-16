<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Form\AttendanceType;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/attendance')]
final class AttendanceController extends AbstractController
{
    #[Route(name: 'app_attendance_index', methods: ['GET', 'POST'])]
    public function index(
        AttendanceRepository $attendanceRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $filter = $request->query->get('filter');
        $query = $attendanceRepository->paginateAttendance($filter);

        $attendances = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('attendance/index.html.twig', [
            'attendances' => $attendances,
        ]);
    }

    #[Route('/new', name: 'app_attendance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $attendance = new Attendance();
	$flash = $translator->trans('Attendance added successfully.');
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

	    $this->addFlash('success', $flash);
            return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('attendance/new.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attendance_show', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(Attendance $attendance): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('attendance/show.html.twig', [
            'attendance' => $attendance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_attendance_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

	$originalCheckOutAt = $attendance->getCheckOutAt();
        $form = $this->createForm(AttendanceType::class, $attendance);
	$flash = $translator->trans('Attendance updated successfully.');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
	    $newCheckOutAt = $attendance->getCheckOutAt();
	    if ($originalCheckOutAt === null && $newCheckOutAt !== null) {
		$attendance->setCheckOutUser($this->getUser());
	    }

            $entityManager->flush();

	    $this->addFlash('primary', $flash);
            return $this->redirectToRoute('app_attendance_show', ['id' => $attendance->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('attendance/edit.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attendance_delete', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(Request $request, Attendance $attendance, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

	$flash = $translator->trans('Attendance deleted successfully.');
        if ($this->isCsrfTokenValid('delete'.$attendance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($attendance);
            $entityManager->flush();
        }

	$this->addFlash('danger', $flash);
        return $this->redirectToRoute('app_attendance_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-out/{redirectRoute}', name: 'app_attendance_check_out', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function checkOut(Request $request, Attendance $attendance, EntityManagerInterface $entityManager, string $redirectRoute, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

	$flash = $translator->trans('Patient check out successfully.');
        if ($this->isCsrfTokenValid('checkout'.$attendance->getId(), $request->getPayload()->getString('_token'))) {
            $attendance->setCheckOutAt(new \DateTimeImmutable());
	    $attendance->setCheckOutUser($this->getUser());
            $entityManager->flush();
        }

        $routeParameters = [];
        if ($redirectRoute === 'app_attendance_show') {
            $routeParameters['id'] = $attendance->getUuid();
        }

	$this->addFlash('primary', $flash);
        return $this->redirectToRoute($redirectRoute, $routeParameters);
    }
}
