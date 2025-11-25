<?php

namespace App\Entity;

use App\Repository\AttendanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
class Attendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['attendance_list', 'attendance_detail', 'patient_detail'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['attendance_list', 'attendance_detail', 'patient_detail'])]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['attendance_list', 'attendance_detail', 'patient_detail'])]
    private ?\DateTimeImmutable $checkOutAt = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['attendance_detail'])]
    private ?Patient $patient = null;

    #[ORM\Column(length: 4)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 4)]
    #[Assert\Regex(
        pattern: '/^[A-Z]\d{3}$/',
        message: 'El gafete debe iniciar con una letra y continuar con tres números'
    )]
    #[Groups(['attendance_list', 'attendance_detail', 'patient_detail'])]
    private ?string $tag = null;

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

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
