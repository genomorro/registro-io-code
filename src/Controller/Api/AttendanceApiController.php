<?php

namespace App\Controller\Api;

use App\Entity\Attendance;
use App\Form\AttendanceType;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class AttendanceApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/attendance', name: 'api_attendance_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(AttendanceRepository $attendanceRepository): Response
    {
        return $this->json($attendanceRepository->findAll(), context: ['groups' => 'Api']);
    }

    #[Route('/attendance', name: 'api_attendance_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $attendance = new Attendance();
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attendance);
            $entityManager->flush();

            return $this->json($attendance, Response::HTTP_CREATED, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/attendance/{id}', name: 'api_attendance_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Attendance $attendance): Response
    {
        return $this->json($attendance, context: ['groups' => 'Api']);
    }

    #[Route('/attendance/{id}', name: 'api_attendance_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json($attendance, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/attendance/{id}', name: 'api_attendance_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($attendance);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
