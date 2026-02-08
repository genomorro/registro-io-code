<?php

namespace App\Twig;

use App\Service\UuidEncoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UuidExtension extends AbstractExtension
{
    public function __construct(
        private readonly UuidEncoder $uuidEncoder,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('encode_uuid', [$this, 'encodeUuid']),
        ];
    }

    public function encodeUuid(int|string|null $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        return $this->uuidEncoder->encode((int) $id);
    }
}
