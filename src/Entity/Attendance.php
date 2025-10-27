<?php

namespace App\Entity;

use App\Repository\AttendanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
class Attendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Api'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['Api'])]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['Api'])]
    private ?\DateTimeImmutable $checkOutAt = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['Api', 'Patient'])]
    private ?Patient $patient = null;

    #[ORM\Column]
    #[Groups(['Api'])]
    private ?int $tag = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

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
}
