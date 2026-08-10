<?php

namespace App\Entity;

use App\Entity\Trait\HasUuidTrait;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    use HasUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $area = null;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\OneToMany(targetEntity: Visitor::class, mappedBy: 'host')]
    private Collection $visitors;

    /**
     * @var Collection<int, Stakeholder>
     */
    #[ORM\OneToMany(targetEntity: Stakeholder::class, mappedBy: 'host')]
    private Collection $stakeholders;

    public function __construct()
    {
        $this->visitors = new ArrayCollection();
        $this->stakeholders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

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
            $visitor->setHost($this);
        }

        return $this;
    }

    public function removeVisitor(Visitor $visitor): static
    {
        if ($this->visitors->removeElement($visitor)) {
            // set the owning side to null (unless already changed)
            if ($visitor->getHost() === $this) {
                $visitor->setHost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Stakeholder>
     */
    public function getStakeholders(): Collection
    {
        return $this->stakeholders;
    }

    public function addStakeholder(Stakeholder $stakeholder): static
    {
        if (!$this->stakeholders->contains($stakeholder)) {
            $this->stakeholders->add($stakeholder);
            $stakeholder->setHost($this);
        }

        return $this;
    }

    public function removeStakeholder(Stakeholder $stakeholder): static
    {
        if ($this->stakeholders->removeElement($stakeholder)) {
            // set the owning side to null (unless already changed)
            if ($stakeholder->getHost() === $this) {
                $stakeholder->setHost(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
