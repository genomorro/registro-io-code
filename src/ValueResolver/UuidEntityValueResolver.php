<?php

namespace App\ValueResolver;

use App\Service\UuidEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UuidEntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UuidEncoder $uuidEncoder
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (!$argumentType || !class_exists($argumentType)) {
            return [];
        }

        $isEntity = false;
        try {
            $this->entityManager->getClassMetadata($argumentType);
            $isEntity = true;
        } catch (\Exception) {
            // Not a doctrine entity
        }

        if (!$isEntity) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());
        if ($value === null && $request->attributes->has('id')) {
            $value = $request->attributes->get('id');
        }

        if (!is_string($value) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            return [];
        }

        $id = $this->uuidEncoder->decode($value);
        if ($id === null) {
            return [];
        }

        $entity = $this->entityManager->find($argumentType, $id);
        if ($entity === null) {
            return [];
        }

        return [$entity];
    }
}
