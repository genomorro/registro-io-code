<?php

namespace App\Entity\Trait;

trait HasUuidTrait
{
    private ?string $uuid = null;

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
