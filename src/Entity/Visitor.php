<?php

namespace App\Entity;

use App\Repository\VisitorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitorRepository::class)]
class Visitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $dni = null;

    #[ORM\Column]
    private ?int $tag = null;

    #[ORM\Column(length: 255)]
    private ?string $destination = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $CheckOutAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $relationship = null;

    /**
     * @var Collection<int, Patient>
     */
    #[ORM\ManyToMany(targetEntity: Patient::class, inversedBy: 'visitors')]
    private Collection $patient;

    public function __construct()
    {
        $this->patient = new ArrayCollection();
    }

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

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(?int $phone): static
    {
        $this->phone = $phone;

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
        return $this->CheckOutAt;
    }

    public function setCheckOutAt(?\DateTimeImmutable $CheckOutAt): static
    {
        $this->CheckOutAt = $CheckOutAt;

        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function setRelationship(?string $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    /**
     * @return Collection<int, Patient>
     */
    public function getPatient(): Collection
    {
        return $this->patient;
    }

    public function addPatient(Patient $patient): static
    {
        if (!$this->patient->contains($patient)) {
            $this->patient->add($patient);
        }

        return $this;
    }

    public function removePatient(Patient $patient): static
    {
        $this->patient->removeElement($patient);

        return $this;
    }
}
