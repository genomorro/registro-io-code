<?php

namespace App\Controller;

use App\Form\SearchTagType;
use App\Form\SearchType;
use App\Repository\PatientRepository;
use App\Repository\SearchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route('/', name: 'app_search_index')]
    public function index(): Response
    {
	return $this->render('search/index.html.twig');
    }
    #[Route('/file', name: 'app_search_file_index')]
    public function searchFile(Request $request, PatientRepository $patientRepository, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
	$flash = $translator->trans('Patient not found');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $data['file'];

            $patient = $patientRepository->findOneByFile($file);

            if ($patient) {
                return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
            }

            $this->addFlash('error', $flash);
	    return $this->redirectToRoute('app_search_file_index');
        }

        return $this->render('search/file.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag', name: 'app_search_tag_index')]
    public function searchByTag(Request $request, SearchRepository $searchRepository, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(SearchTagType::class);
        $form->handleRequest($request);

        $patients = [];
        $visitors = [];
        $searched = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tag = $data['tag'];

            $patients = $searchRepository->findPatientsByTag($tag);
            $visitors = $searchRepository->findVisitorsByTag($tag);
            $searched = true;
        }

        return $this->render('search/tag.html.twig', [
            'form' => $form->createView(),
            'patients' => $patients,
            'visitors' => $visitors,
            'title' => 'Search by Tag',
            'searched' => $searched,
        ]);
    }
}
