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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/patient')]
final class PatientController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route(name: 'api_patient_index', methods: ['GET'])]
    public function index(PatientRepository $patientRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $patients = $patientRepository->findAll();
        $json = $this->serializer->serialize($patients, 'json', [
            'groups' => 'patient_list',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'api_patient_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patient);
            $entityManager->flush();

            $json = $this->serializer->serialize($patient, 'json', [
                'groups' => 'patient_detail',
            ]);

            return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_patient_show', methods: ['GET'])]
    public function show(Patient $patient): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $json = $this->serializer->serialize($patient, 'json', [
            'groups' => 'patient_detail',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'api_patient_edit', methods: ['PUT'])]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(PatientType::class, $patient);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $json = $this->serializer->serialize($patient, 'json', [
                'groups' => 'patient_detail',
            ]);

            return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_patient_delete', methods: ['DELETE'])]
    public function delete(Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $entityManager->remove($patient);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
