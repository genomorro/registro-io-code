<?php

namespace App\ValueResolver;

use App\Service\UuidEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class UuidEntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly UuidEncoder $uuidEncoder,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (!$type || !class_exists($type) || !str_starts_with($type, 'App\Entity\\')) {
            return [];
        }

        $id = $request->attributes->get('id');
        if (!$id || !is_string($id) || !Uuid::isValid($id)) {
            // If it's not a UUID, we don't handle it, maybe it's a legacy integer ID
            return [];
        }

        $decodedId = $this->uuidEncoder->decode($id);
        if ($decodedId === null) {
            return [];
        }

        $entity = $this->entityManager->getRepository($type)->find($decodedId);
        if (!$entity && !$argument->isNullable()) {
            throw new NotFoundHttpException(sprintf('"%s" object not found by uuid "%s".', $type, $id));
        }

        return [$entity];
    }
}
