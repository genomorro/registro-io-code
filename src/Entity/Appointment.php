<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?string $agenda = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?string $specialty = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?\DateTimeImmutable $date_at = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment_list', 'appointment_detail', 'patient_detail'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['appointment_detail'])]
    private ?Patient $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgenda(): ?string
    {
        return $this->agenda;
    }

    public function setAgenda(string $agenda): static
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): static
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDateAt(): ?\DateTimeImmutable
    {
        return $this->date_at;
    }

    public function setDateAt(\DateTimeImmutable $date_at): static
    {
        $this->date_at = $date_at;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
