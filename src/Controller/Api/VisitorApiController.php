<?php

namespace App\Controller\Api;

use App\Entity\Visitor;
use App\Form\VisitorType;
use App\Repository\VisitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class VisitorApiController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/visitor', name: 'api_visitor_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(VisitorRepository $visitorRepository): Response
    {
        return $this->json($visitorRepository->findAll(), context: ['groups' => 'Api']);
    }

    #[Route('/visitor', name: 'api_visitor_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $visitor = new Visitor();
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($visitor);
            $entityManager->flush();

            return $this->json($visitor, Response::HTTP_CREATED, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/visitor/{id}', name: 'api_visitor_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Visitor $visitor): Response
    {
        return $this->json($visitor, context: ['groups' => 'Api']);
    }

    #[Route('/visitor/{id}', name: 'api_visitor_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json($visitor, context: ['groups' => 'Api']);
        }

        return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/visitor/{id}', name: 'api_visitor_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($visitor);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
