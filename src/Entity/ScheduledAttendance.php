<?php

namespace App\Entity;

use App\Entity\Trait\HasUuidTrait;
use App\Repository\ScheduledAttendanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduledAttendanceRepository::class)]
class ScheduledAttendance
{
    use HasUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'scheduledAttendances')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Please select a Scheduled.')]
    private ?Scheduled $scheduled = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $checkInAt = null;

    #[ORM\ManyToOne(inversedBy: 'scheduledAttendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $checkInUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheduled(): ?Scheduled
    {
        return $this->scheduled;
    }

    public function setScheduled(?Scheduled $scheduled): static
    {
        $this->scheduled = $scheduled;

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

    public function getCheckInUser(): ?User
    {
        return $this->checkInUser;
    }

    public function setCheckInUser(?User $checkInUser): static
    {
        $this->checkInUser = $checkInUser;

        return $this;
    }
}
