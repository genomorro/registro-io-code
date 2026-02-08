<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Visitor;
use App\Form\VisitorType;
use App\Repository\VisitorRepository;
use App\Service\UuidEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/visitor')]
final class VisitorController extends AbstractController
{
    #[Route(name: 'app_visitor_index', methods: ['GET'])]
    public function index(
        VisitorRepository $visitorRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $filter = $request->query->get('filter');
        $query = $visitorRepository->paginateVisitor($filter);

        $visitors = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('visitor/index.html.twig', [
            'visitors' => $visitors,
        ]);
    }

    #[Route('/new', name: 'app_visitor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UuidEncoder $uuidEncoder): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $visitor = new Visitor();
        $patientId = $request->query->get('patientId');
        if ($patientId) {
            $id = $patientId;
            if (Uuid::isValid($patientId)) {
                $id = $uuidEncoder->decode($patientId);
            }
            $patient = $entityManager->getRepository(Patient::class)->find($id);
            if ($patient) {
                $visitor->addPatient($patient);
            }
        }
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visitor->setCheckInAt(new \DateTimeImmutable());
            $visitor->setCheckInUser($this->getUser());

            $entityManager->persist($visitor);
            $entityManager->flush();

            $evidenceData = $form->get('evidence')->getData();
            if ($evidenceData) {
                $data = explode(',', $evidenceData);
                $imageData = base64_decode($data[1]);
                
                $checkInAt = $visitor->getCheckInAt();
                $year = $checkInAt->format('Y');
                $month = $checkInAt->format('m');
                
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/visitor/' . $year . '/' . $month;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $filename = $visitor->getId() . '-' . $checkInAt->format('YmdHis') . '.png';
                $filepath = $uploadDir . '/' . $filename;
                
                file_put_contents($filepath, $imageData);
                
                $visitor->setEvidence('/uploads/visitor/' . $year . '/' . $month . '/' . $filename);
		$entityManager->flush();
            }

            return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visitor/new.html.twig', [
            'visitor' => $visitor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visitor_show', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function show(Visitor $visitor): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('visitor/show.html.twig', [
            'visitor' => $visitor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visitor_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        $originalCheckOutAt = $visitor->getCheckOutAt();
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newCheckOutAt = $visitor->getCheckOutAt();
            if ($originalCheckOutAt === null && $newCheckOutAt !== null) {
                $visitor->setCheckOutUser($this->getUser());
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visitor/edit.html.twig', [
            'visitor' => $visitor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visitor_delete', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$visitor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visitor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visitor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-out', name: 'app_visitor_check_out', methods: ['POST'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function checkOut(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
	$this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isCsrfTokenValid('checkout'.$visitor->getId(), $request->getPayload()->getString('_token'))) {
            $visitor->setCheckOutAt(new \DateTimeImmutable());
            $visitor->setCheckOutUser($this->getUser());
            $entityManager->flush();
        }

        $redirectRoute = $request->query->get('redirect_route', 'app_visitor_index');
        $routeParameters = [];

        switch ($redirectRoute) {
            case 'app_visitor_show':
                $routeParameters['id'] = $visitor->getUuid();
                break;
            case 'app_search_check_index':
                $routeParameters['tag'] = $request->query->get('tag');
                break;
            case 'app_search_name_index':
                $routeParameters['name'] = $request->query->get('name');
                break;
        }

        return $this->redirectToRoute($redirectRoute, $routeParameters);
    }
}
