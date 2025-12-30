<?php

namespace App\Entity;

use App\Repository\StakeholderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StakeholderRepository::class)]
class Stakeholder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $dni = null;

    #[ORM\Column]
    private ?int $tag = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    private ?string $destination = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $checkOutAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $evidence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sign = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getTag(): ?int
    {
        return $this->tag;
    }

    public function setTag(int $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getCheckInAt(): ?\DateTimeImmutable
    {
        return $this->checkInAt;
    }

    public function setCheckInAt(\DateTimeImmutable $checkInAt): static
    {
        $this->checkInAt = $checkInAt;

        return $this;
    }

    public function getCheckOutAt(): ?\DateTimeImmutable
    {
        return $this->checkOutAt;
    }

    public function setCheckOutAt(?\DateTimeImmutable $checkOutAt): static
    {
        $this->checkOutAt = $checkOutAt;

        return $this;
    }

    public function getEvidence(): ?string
    {
        return $this->evidence;
    }

    public function setEvidence(?string $evidence): static
    {
        $this->evidence = $evidence;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(?string $sign): static
    {
        $this->sign = $sign;

        return $this;
    }
}
