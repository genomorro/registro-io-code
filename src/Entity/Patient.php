<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[UniqueEntity(fields: ['file'], message: 'There is already a patient with this file')]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['patient_list', 'patient_detail', 'appointment_detail', 'attendance_list', 'attendance_detail', 'visitor_list', 'visitor_detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    #[Groups(['patient_list', 'patient_detail', 'appointment_detail', 'attendance_list', 'attendance_detail', 'visitor_list', 'visitor_detail'])]
    private ?string $file = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient_list', 'patient_detail', 'appointment_detail', 'attendance_list', 'attendance_detail', 'visitor_list', 'visitor_detail'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['patient_detail'])]
    private ?bool $disability = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'patient', orphanRemoval: true)]
    #[Groups(['patient_detail'])]
    private Collection $appointments;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'patient', orphanRemoval: true)]
    #[Groups(['patient_detail'])]
    private Collection $attendances;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\ManyToMany(targetEntity: Visitor::class, mappedBy: 'patient')]
    #[Groups(['patient_detail'])]
    private Collection $visitors;

    #[ORM\OneToOne(mappedBy: 'patient', cascade: ['persist', 'remove'])]
    private ?Hospitalized $hospitalized = null;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
        $this->attendances = new ArrayCollection();
        $this->visitors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
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

    public function isDisability(): ?bool
    {
        return $this->disability;
    }

    public function setDisability(bool $disability): static
    {
        $this->disability = $disability;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setPatient($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getPatient() === $this) {
                $appointment->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setPatient($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getPatient() === $this) {
                $attendance->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Visitor>
     */
    public function getVisitors(): Collection
    {
        return $this->visitors;
    }

    public function addVisitor(Visitor $visitor): static
    {
        if (!$this->visitors->contains($visitor)) {
            $this->visitors->add($visitor);
            $visitor->addPatient($this);
        }

        return $this;
    }

    public function removeVisitor(Visitor $visitor): static
    {
        if ($this->visitors->removeElement($visitor)) {
            $visitor->removePatient($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getHospitalized(): ?Hospitalized
    {
        return $this->hospitalized;
    }

    public function setHospitalized(Hospitalized $hospitalized): static
    {
        // set the owning side of the relation if necessary
        if ($hospitalized->getPatient() !== $this) {
            $hospitalized->setPatient($this);
        }

        $this->hospitalized = $hospitalized;

        return $this;
    }
}
