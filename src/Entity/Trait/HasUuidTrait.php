<?php

namespace App\Entity\Trait;

use App\Service\UuidEncoder;

trait HasUuidTrait
{
    private ?string $uuid = null;

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;
        return $this;
    }
}
