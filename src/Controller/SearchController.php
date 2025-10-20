<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\PatientRepository;
use App\Repository\VisitorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, PatientRepository $patientRepository, VisitorRepository $visitorRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        $patients = [];
        $visitors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $query = $data['query'];
            $criteria = $data['criteria'];

            if (empty($query)) {
                $this->addFlash('warning', 'Search query cannot be empty.');
            } else {
                switch ($criteria) {
                    case 'patient_name':
                        $patients = $patientRepository->findByNameAndAppointmentToday($query);
                        break;
                    case 'patient_file':
                        $patients = $patientRepository->findByFileAndAppointmentToday($query);
                        break;
                    case 'patient_tag':
                        if (is_numeric($query)) {
                            $patients = $patientRepository->findByTagAndAppointmentToday((int)$query);
                        } else {
                            $this->addFlash('error', 'Tag must be a number.');
                        }
                        break;
                    case 'visitor_name':
                        $visitors = $visitorRepository->findByNameAndCheckInToday($query);
                        break;
                    case 'visitor_tag':
                        if (is_numeric($query)) {
                            $visitors = $visitorRepository->findByTagAndCheckInToday((int)$query);
                        } else {
                            $this->addFlash('error', 'Tag must be a number.');
                        }
                        break;
                }
            }
        }

        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
            'patients' => $patients,
            'visitors' => $visitors,
        ]);
    }
}
