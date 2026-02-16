<?php

namespace App\EventListener;

use App\Service\UuidEncoder;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::postPersist)]
class EntityUuidListener
{
    public function __construct(
        private readonly UuidEncoder $uuidEncoder
    ) {
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->updateUuid($args->getObject());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->updateUuid($args->getObject());
    }

    private function updateUuid(object $entity): void
    {
        if (method_exists($entity, 'setUuid') && method_exists($entity, 'getId')) {
            $id = $entity->getId();
            if ($id !== null) {
                $entity->setUuid($this->uuidEncoder->encode($id));
            }
        }
    }
}
