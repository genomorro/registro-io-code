<?php

namespace App\Controller;

use App\Form\SearchTagType;
use App\Form\SearchType;
use App\Repository\AttendanceRepository;
use App\Repository\PatientRepository;
use App\Repository\VisitorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route('/file', name: 'app_search_file_index')]
    public function searchFile(Request $request, PatientRepository $patientRepository, TranslatorInterface $translator): Response
    {
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

    #[Route('/patient_tag', name: 'app_search_patient_tag_index')]
    public function searchPatientByTag(Request $request, AttendanceRepository $attendanceRepository, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(SearchTagType::class);
        $form->handleRequest($request);
	$flash = $translator->trans('Patient not found for the given tag.');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tag = (int)$data['tag'];

            $patient = $attendanceRepository->findPatientByTag($tag);

            if ($patient) {
                return $this->redirectToRoute('app_patient_show', ['id' => $patient->getId()]);
            }

            $this->addFlash('error', $flash);
            return $this->redirectToRoute('app_search_patient_tag_index');
        }

        return $this->render('search/tag.html.twig', [
            'form' => $form->createView(),
	    'title' => "Search Patient by Tag",
        ]);
    }

    #[Route('/visitor_tag', name: 'app_search_visitor_tag_index')]
    public function searchVisitorByTag(Request $request, VisitorRepository $visitorRepository, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(SearchTagType::class);
        $form->handleRequest($request);
	$flash = $translator->trans('Visitor not found for the given tag.');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tag = (int)$data['tag'];

            $visitor = $visitorRepository->findOneByTag($tag);

            if ($visitor) {
                return $this->redirectToRoute('app_visitor_show', ['id' => $visitor->getId()]);
            }

            $this->addFlash('error', $flash);
            return $this->redirectToRoute('app_search_visitor_tag_index');
        }

        return $this->render('search/tag.html.twig', [
            'form' => $form->createView(),
	    'title' => "Search Visitor by Tag",
        ]);
    }
}
