<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/appointment')]
final class AppointmentController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route(name: 'api_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $appointments = $appointmentRepository->findAll();
        $json = $this->serializer->serialize($appointments, 'json', [
            'groups' => 'appointment_list',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'api_appointment_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            $json = $this->serializer->serialize($appointment, 'json', [
                'groups' => 'appointment_detail',
            ]);

            return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $json = $this->serializer->serialize($appointment, 'json', [
            'groups' => 'appointment_detail',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'api_appointment_edit', methods: ['PUT'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $json = $this->serializer->serialize($appointment, 'json', [
                'groups' => 'appointment_detail',
            ]);

            return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_appointment_delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $entityManager->remove($appointment);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
