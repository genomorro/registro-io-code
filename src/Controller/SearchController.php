<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\PatientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
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

        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
