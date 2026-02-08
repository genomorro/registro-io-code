<?php

namespace App\Entity;

use App\Entity\Trait\HasUuidTrait;

use App\Repository\HospitalizedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HospitalizedRepository::class)]
class Hospitalized
{
    use HasUuidTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $service = null;

    #[ORM\Column(length: 255)]
    private ?string $bed = null;

    #[ORM\OneToOne(inversedBy: 'hospitalized', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getBed(): ?string
    {
        return $this->bed;
    }

    public function setBed(string $bed): static
    {
        $this->bed = $bed;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }
}
