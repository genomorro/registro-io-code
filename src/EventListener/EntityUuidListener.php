<?php

namespace App\EventListener;

use App\Service\UuidEncoder;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::postPersist)]
class EntityUuidListener
{
    public function __construct(
        private readonly UuidEncoder $uuidEncoder,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->updateUuid($args->getObject());
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->updateUuid($args->getObject());
    }

    private function updateUuid(object $entity): void
    {
        // Check if entity has the setUuid method (uses HasUuidTrait)
        if (method_exists($entity, 'setUuid') && method_exists($entity, 'getId')) {
            $id = $entity->getId();
            if ($id !== null) {
                $entity->setUuid($this->uuidEncoder->encode($id));
            }
        }
    }
}
