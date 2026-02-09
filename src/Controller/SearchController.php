<?php

namespace App\Controller;

use App\Form\SearchFileType;
use App\Form\SearchNameType;
use App\Form\SearchTagType;
use App\Repository\AttendanceRepository;
use App\Repository\PatientRepository;
use App\Repository\SearchRepository;
use App\Repository\VisitorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route('/', name: 'app_search_index')]
    public function index(Request $request, PatientRepository $patientRepository, SearchRepository $searchRepository, VisitorRepository $visitorRepository, TranslatorInterface $translator): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

	$formFile = $this->createForm(SearchFileType::class);
        $formFile->handleRequest($request);

        if ($formFile->isSubmitted() && $formFile->isValid()) {
            $data = $formFile->getData();
            $file = $data['file'];
	    
	    $patient = $patientRepository->findByFile($file);

	    if (count($patient) === 1) {
		return $this->redirectToRoute('app_patient_show', ['id' => $patient[0]->getUuid()]);
	    } elseif (empty($patient)) {
		$flashFile = $translator->trans('Patient not found');
	    } else {
		$flashFile = $translator->trans('Improve the search criteria');
	    }

	    $this->addFlash('error', $flashFile);
	    return $this->redirectToRoute('app_search_index');
        }


	$formName = $this->createForm(SearchNameType::class);
        $formName->handleRequest($request);
	$flashName = $translator->trans('Patient not found for the given name.');

        if ($formName->isSubmitted() && $formName->isValid()) {
	    $data = $formName->getData();
	    $name = $data['name'];

            $patients = $patientRepository->findByName($name);
            $visitors = $visitorRepository->findByName($name);

	    if ($patients or $visitors) {
		return $this->redirectToRoute('app_search_name_index', ['name' => $name]);
	    }

	    $this->addFlash('error', $flashName);
	    return $this->redirectToRoute('app_search_index');
	}

	
        $formTag = $this->createForm(SearchTagType::class);
        $formTag->handleRequest($request);
	$flashTag = $translator->trans('Patient nor Visitor not found for the given tag.');

        if ($formTag->isSubmitted() && $formTag->isValid()) {
	    $data = $formTag->getData();
	    $tag = (int)$data['tag'];

	    $patients = $searchRepository->findCurrentPatientsByTag($tag);
	    $visitors = $searchRepository->findCurrentVisitorsByTag($tag);

	    if ($patients or $visitors) {
		return $this->redirectToRoute('app_search_check_index', ['tag' => $tag]);
	    }

	    $this->addFlash('error', $flashTag);
	    return $this->redirectToRoute('app_search_index');
        }

	
        return $this->render('search/index.html.twig', [
	    'formFile' => $formFile->createView(),
	    'formName' => $formName->createView(),
	    'formTag' => $formTag->createView(),
        ]);
    }

    #[Route('/check-out-list', name: 'app_search_check_list_index')]
    public function checkOutList(SearchRepository $searchRepository, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $patients = $searchRepository->findCurrentPatients();
        $visitors = $searchRepository->findCurrentVisitors();

        return $this->render('search/check_out.html.twig', [
	    'patients' => $patients,
	    'visitors' => $visitors,
	    'title' => $translator->trans('Check Out List'),
        ]);
    }
    
    #[Route('/tag/{tag}/check-out', name: 'app_search_check_index')]
    public function checkOut(string $tag, SearchRepository $searchRepository, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $patients = $searchRepository->findCurrentPatientsByTag($tag);
        $visitors = $searchRepository->findCurrentVisitorsByTag($tag);

        return $this->render('search/check_out.html.twig', [
	    'patients' => $patients,
	    'visitors' => $visitors,
	    'tag' => $tag,
	    'title' => $translator->trans('Check Out by Tag'),
        ]);
    }

    #[Route('/name/{name}/check-out', name: 'app_search_name_check_out')]
    public function name(string $name, SearchRepository $searchRepository, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $patients = $searchRepository->findCurrentPatientsByName($name);
        $visitors = $searchRepository->findCurrentVisitorsByName($name);

        return $this->render('search/check_out.html.twig', [
	    'patients' => $patients,
	    'visitors' => $visitors,
	    'name' => $name,
	    'title' => $translator->trans('Check Out by Name'),
        ]);
    }

    #[Route('/name/{name}', name: 'app_search_name_index')]
    public function nameAll(string $name, PatientRepository $patientRepository, VisitorRepository $visitorRepository, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $patients = $patientRepository->findByName($name);
        $visitors = $visitorRepository->findByName($name);

        return $this->render('search/name.html.twig', [
	    'patients' => $patients,
	    'visitors' => $visitors,
	    'name' => $name,
	    'title' => $translator->trans('Search by Name'),
        ]);
    }
}
