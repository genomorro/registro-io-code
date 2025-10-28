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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/attendance')]
final class AttendanceController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route(name: 'api_attendance_index', methods: ['GET'])]
    public function index(AttendanceRepository $attendanceRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $attendances = $attendanceRepository->findAll();
        $json = $this->serializer->serialize($attendances, 'json', [
            'groups' => 'attendance_list',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'api_attendance_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $attendance = new Attendance();
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attendance);
            $entityManager->flush();

            $json = $this->serializer->serialize($attendance, 'json', [
                'groups' => 'attendance_detail',
            ]);

            return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_attendance_show', methods: ['GET'])]
    public function show(Attendance $attendance): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $json = $this->serializer->serialize($attendance, 'json', [
            'groups' => 'attendance_detail',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'api_attendance_edit', methods: ['PUT'])]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $json = $this->serializer->serialize($attendance, 'json', [
                'groups' => 'attendance_detail',
            ]);

            return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_attendance_delete', methods: ['DELETE'])]
    public function delete(Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $entityManager->remove($attendance);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
