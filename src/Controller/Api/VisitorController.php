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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/visitor')]
final class VisitorController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route(name: 'api_visitor_index', methods: ['GET'])]
    public function index(VisitorRepository $visitorRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $visitors = $visitorRepository->findAll();
        $json = $this->serializer->serialize($visitors, 'json', [
            'groups' => 'visitor_list',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'api_visitor_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $visitor = new Visitor();
        $form = $this->createForm(VisitorType::class, $visitor);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($visitor);
            $entityManager->flush();

            $json = $this->serializer->serialize($visitor, 'json', [
                'groups' => 'visitor_detail',
            ]);

            return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_visitor_show', methods: ['GET'])]
    public function show(Visitor $visitor): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $json = $this->serializer->serialize($visitor, 'json', [
            'groups' => 'visitor_detail',
        ]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'api_visitor_edit', methods: ['PUT'])]
    public function edit(Request $request, Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(VisitorType::class, $visitor);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $json = $this->serializer->serialize($visitor, 'json', [
                'groups' => 'visitor_detail',
            ]);

            return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $form->getErrors(true, false), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_visitor_delete', methods: ['DELETE'])]
    public function delete(Visitor $visitor, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $entityManager->remove($visitor);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
