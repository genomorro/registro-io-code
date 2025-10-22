<?php

namespace App\Controller;

use App\Form\SearchTagType;
use App\Form\SearchType;
use App\Repository\AttendanceRepository;
use App\Repository\PatientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig');
    }

    #[Route('/search_file', name: 'app_search_file_index')]
    public function searchFile(Request $request, PatientRepository $patientRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $data['file'];

            $patient = $patientRepository->findOneByFile($file);

            if ($patient) {
                return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
            }

            $this->addFlash('error', 'Patient not found');
	    return $this->redirectToRoute('app_search_file_index');
        }

        return $this->render('search/file.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search_patient_tag', name: 'app_search_patient_tag_index')]
    public function searchPatientByTag(Request $request, AttendanceRepository $attendanceRepository): Response
    {
        $form = $this->createForm(SearchTagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tag = (int)$data['tag'];

            $patient = $attendanceRepository->findPatientByTag($tag);

            if ($patient) {
                return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
            }

            $this->addFlash('error', 'Patient not found for the given tag.');
            return $this->redirectToRoute('app_search_tag_index');
        }

        return $this->render('search/tag.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
