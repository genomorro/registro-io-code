<?php

namespace App\Controller\Api;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class PatientApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/patient', name: 'api_patient_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(PatientRepository $patientRepository): Response
    {
        return $this->json($patientRepository->findAll(), context: ['groups' => 'Api']);
    }

    #[Route('/patient', name: 'api_patient_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient();
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(PatientType::class, $patient);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->json($patient, Response::HTTP_CREATED, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/patient/{id}', name: 'api_patient_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Patient $patient): Response
    {
        return $this->json($patient, context: ['groups' => 'Api']);
    }

    #[Route('/patient/{id}', name: 'api_patient_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(PatientType::class, $patient);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json($patient, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/patient/{id}', name: 'api_patient_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($patient);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
