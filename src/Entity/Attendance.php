<?php

namespace App\Entity;

use App\Repository\AttendanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
class Attendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $checkOutAt = null;

    #[ORM\Column]
    private ?int $tag = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $evidence = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'attendancesCheckIn')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $checkInUser = null;

    #[ORM\ManyToOne(inversedBy: 'attendancesCheckOut')]
    private ?User $checkOutUser = null;

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

    public function getTag(): ?int
    {
        return $this->tag;
    }

    public function setTag(int $tag): static
    {
        $this->tag = $tag;

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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getCheckInUser(): ?User
    {
        return $this->checkInUser;
    }

    public function setCheckInUser(?User $checkInUser): static
    {
        $this->checkInUser = $checkInUser;

        return $this;
    }

    public function getCheckOutUser(): ?User
    {
        return $this->checkOutUser;
    }

    public function setCheckOutUser(?User $checkOutUser): static
    {
        $this->checkOutUser = $checkOutUser;

        return $this;
    }
}
