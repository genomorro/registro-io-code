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
    private ?\DateTimeImmutable $checkin_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $checkout_at = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckinAt(): ?\DateTimeImmutable
    {
        return $this->checkin_at;
    }

    public function setCheckinAt(\DateTimeImmutable $checkin_at): static
    {
        $this->checkin_at = $checkin_at;

        return $this;
    }

    public function getCheckoutAt(): ?\DateTimeImmutable
    {
        return $this->checkout_at;
    }

    public function setCheckoutAt(?\DateTimeImmutable $checkout_at): static
    {
        $this->checkout_at = $checkout_at;

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
}
