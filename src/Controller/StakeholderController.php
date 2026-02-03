<?php

namespace App\Controller;

use App\Entity\Stakeholder;
use App\Form\StakeholderType;
use App\Repository\StakeholderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stakeholder')]
final class StakeholderController extends AbstractController
{
    #[Route(name: 'app_stakeholder_index', methods: ['GET'])]
    public function index(
        StakeholderRepository $stakeholderRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $filter = $request->query->get('filter');
        $query = $stakeholderRepository->paginateStakeholder($filter);

        $stakeholders = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('stakeholder/index.html.twig', [
            'stakeholders' => $stakeholders,
        ]);
    }

    #[Route('/new', name: 'app_stakeholder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stakeholder = new Stakeholder();
        $form = $this->createForm(StakeholderType::class, $stakeholder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stakeholder->setCheckInUser($this->getUser());

            $entityManager->persist($stakeholder);
            $entityManager->flush();

            $checkInAt = $form->get('checkInAt')->getData();
            if ($checkInAt instanceof \DateTime) {
                $checkInAtImmutable = \DateTimeImmutable::createFromMutable($checkInAt);
                $this->handleImageUpload($form, 'evidence', $stakeholder, $entityManager, $checkInAtImmutable);
                $this->handleImageUpload($form, 'sign', $stakeholder, $entityManager, $checkInAtImmutable);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stakeholder/new.html.twig', [
            'stakeholder' => $stakeholder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stakeholder_show', methods: ['GET'])]
    public function show(Stakeholder $stakeholder): Response
    {
        return $this->render('stakeholder/show.html.twig', [
            'stakeholder' => $stakeholder,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_stakeholder_pdf', methods: ['GET'])]
    public function exportPdf(Stakeholder $stakeholder, DompdfWrapperInterface $wrapper): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $publicDir = $projectDir . '/public';

        $backgroundBase64 = null;
        $backgroundPath = $projectDir . '/assets/images/fondo-2026.jpeg';
        if (file_exists($backgroundPath)) {
            $data = file_get_contents($backgroundPath);
            $backgroundBase64 = 'data:image/jpeg;base64,' . base64_encode($data);
        }

        $evidenceBase64 = null;
        if ($stakeholder->getEvidence()) {
            $path = $publicDir . $stakeholder->getEvidence();
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $evidenceBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $signBase64 = null;
        if ($stakeholder->getSign()) {
            $path = $publicDir . $stakeholder->getSign();
            if (file_exists($path)) {
                $data = file_get_contents($path);
                $signBase64 = 'data:image/svg+xml;base64,' . base64_encode($data);
            }
        }

        $html = $this->renderView('stakeholder/pdf.html.twig', [
            'stakeholder' => $stakeholder,
            'evidence_base64' => $evidenceBase64,
            'sign_base64' => $signBase64,
            'background_base64' => $backgroundBase64,
        ]);

        $pdfContent = $wrapper->getPdf($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="stakeholder-%d.pdf"', $stakeholder->getId()),
        ]);
    }

    private function handleImageUpload($form, string $fieldName, Stakeholder $stakeholder, EntityManagerInterface $entityManager, \DateTimeImmutable $checkInAt): void
    {
        $imageData = $form->get($fieldName)->getData();
        if ($imageData) {
            $data = explode(',', $imageData);
            $decodedImage = base64_decode($data[1]);
            
            $year = $checkInAt->format('Y');
            $month = $checkInAt->format('m');
            
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/stakeholder/' . $year . '/' . $month;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = sprintf(
                '%d-%s.%s',
                $stakeholder->getId(),
                $checkInAt->format('YmdHis'),
                $fieldName === 'sign' ? 'svg' : 'png'
            );
            $filepath = $uploadDir . '/' . $filename;
            
            file_put_contents($filepath, $decodedImage);
            
            $setter = 'set' . ucfirst($fieldName);
            $stakeholder->$setter('/uploads/stakeholder/' . $year . '/' . $month . '/' . $filename);
        }
    }

    #[Route('/{id}/edit', name: 'app_stakeholder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stakeholder $stakeholder, EntityManagerInterface $entityManager): Response
    {
        $originalCheckOutAt = $stakeholder->getCheckOutAt();
        $form = $this->createForm(StakeholderType::class, $stakeholder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newCheckOutAt = $stakeholder->getCheckOutAt();
            if ($originalCheckOutAt === null && $newCheckOutAt !== null) {
		$stakeholder->setCheckOutUser($this->getUser());
	    }

            $entityManager->flush();

            return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stakeholder/edit.html.twig', [
            'stakeholder' => $stakeholder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stakeholder_delete', methods: ['POST'])]
    public function delete(Request $request, Stakeholder $stakeholder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stakeholder->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stakeholder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stakeholder_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/check-out', name: 'app_stakeholder_check_out', methods: ['POST'])]
    public function checkOut(Request $request, Stakeholder $stakeholder, EntityManagerInterface $entityManager): Response
    {
	/* $this->denyAccessUnlessGranted('ROLE_USER'); */

        if ($this->isCsrfTokenValid('checkout'.$stakeholder->getId(), $request->getPayload()->getString('_token'))) {
            $stakeholder->setCheckOutAt(new \DateTimeImmutable());
            $stakeholder->setCheckOutUser($this->getUser());
            $entityManager->flush();
        }

        $redirectRoute = $request->query->get('redirect_route', 'app_stakeholder_index');
        $routeParameters = [];

        switch ($redirectRoute) {
            case 'app_stakeholder_show':
                $routeParameters['id'] = $stakeholder->getId();
                break;
        }

        return $this->redirectToRoute($redirectRoute, $routeParameters);
    }
}
