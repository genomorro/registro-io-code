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
    #[Groups(['Api', 'Appointment', 'Attendance', 'Visitor'])]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    #[Groups(['Api', 'Appointment', 'Attendance', 'Visitor'])]
    private ?string $file = null;

    #[ORM\Column(length: 255)]
    #[Groups(['Api', 'Appointment', 'Attendance', 'Visitor'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['Api', 'Appointment', 'Attendance', 'Visitor'])]
    private ?bool $disability = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $appointments;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $attendances;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\ManyToMany(targetEntity: Visitor::class, mappedBy: 'patient')]
    private Collection $visitors;

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
}
