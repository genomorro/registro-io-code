<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PatientRepository;
use App\Repository\AttendanceRepository;
use App\Repository\VisitorRepository;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/search')]
class SearchController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route('/file/{file}', name: 'api_search_file', methods: ['GET'])]
    public function searchFile(string $file, PatientRepository $patientRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $patient = $patientRepository->findOneBy(['file' => $file]);

        if (!$patient) {
            return $this->json(['message' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($patient, 'json', ['groups' => 'patient_detail']);
        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/patient_by_tag/{tag}', name: 'api_search_patient_by_tag', methods: ['GET'])]
    public function searchPatientByTag(int $tag, AttendanceRepository $attendanceRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $patient = $attendanceRepository->findPatientByTag($tag);

        if (!$patient) {
            return $this->json(['message' => 'Patient not found for the given tag'], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($patient, 'json', ['groups' => 'patient_detail']);
        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/visitor_by_tag/{tag}', name: 'api_search_visitor_by_tag', methods: ['GET'])]
    public function searchVisitorByTag(int $tag, VisitorRepository $visitorRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $visitor = $visitorRepository->findOneBy(['tag' => $tag]);

        if (!$visitor) {
            return $this->json(['message' => 'Visitor not found for the given tag'], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($visitor, 'json', ['groups' => 'visitor_detail']);
        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
