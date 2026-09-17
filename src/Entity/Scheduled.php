<?php

namespace App\Entity;

use App\Entity\Trait\HasUuidTrait;
use App\Repository\ScheduledRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduledRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LABEL', fields: ['label'])]
#[UniqueEntity(fields: ['label'], message: 'There is already a scheduled item with this label.')]
class Scheduled
{
    use HasUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $institution = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $beginAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'beginAt', message: 'The end date must be equal or greater than the begin date.')]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\ManyToOne(inversedBy: 'scheduleds')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Please select an area.')]
    private ?Area $area = null;

    /**
     * @var Collection<int, ScheduledAttendance>
     */
    #[ORM\OneToMany(targetEntity: ScheduledAttendance::class, mappedBy: 'scheduled')]
    private Collection $scheduledAttendances;

    public function __construct()
    {
        $this->scheduledAttendances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): static
    {
        $this->institution = $institution;

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

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeImmutable $beginAt): static
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection<int, ScheduledAttendance>
     */
    public function getScheduledAttendances(): Collection
    {
        return $this->scheduledAttendances;
    }

    public function addScheduledAttendance(ScheduledAttendance $scheduledAttendance): static
    {
        if (!$this->scheduledAttendances->contains($scheduledAttendance)) {
            $this->scheduledAttendances->add($scheduledAttendance);
            $scheduledAttendance->setScheduled($this);
        }

        return $this;
    }

    public function removeScheduledAttendance(ScheduledAttendance $scheduledAttendance): static
    {
        if ($this->scheduledAttendances->removeElement($scheduledAttendance)) {
            // set the owning side to null (unless already changed)
            if ($scheduledAttendance->getScheduled() === $this) {
                $scheduledAttendance->setScheduled(null);
            }
        }

        return $this;
    }

    public function isWithinDateRange(?\DateTimeInterface $date = null): bool
    {
        $date ??= new \DateTimeImmutable();
        $targetDate = $date->format('Y-m-d');

        $begin = $this->beginAt ? $this->beginAt->format('Y-m-d') : null;
        $end = $this->endAt ? $this->endAt->format('Y-m-d') : null;

        if ($begin && $targetDate < $begin) {
            return false;
        }
        if ($end && $targetDate > $end) {
            return false;
        }

        return true;
    }
}
