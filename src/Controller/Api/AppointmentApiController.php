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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class AppointmentApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/appointment', name: 'api_appointment_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->json($appointmentRepository->findAll(), context: ['groups' => 'Api']);
    }

    #[Route('/appointment', name: 'api_appointment_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->json($appointment, Response::HTTP_CREATED, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/appointment/{id}', name: 'api_appointment_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Appointment $appointment): Response
    {
        return $this->json($appointment, context: ['groups' => 'Api']);
    }

    #[Route('/appointment/{id}', name: 'api_appointment_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json($appointment, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/appointment/{id}', name: 'api_appointment_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($appointment);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
