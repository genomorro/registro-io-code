<?php

namespace App\Entity;

use App\Entity\Trait\HasUuidTrait;
use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    use HasUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $building = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unit = null;

    #[ORM\Column(nullable: true)]
    private ?int $extension = null;

    /**
     * @var Collection<int, Employee>
     */
    #[ORM\OneToMany(targetEntity: Employee::class, mappedBy: 'area')]
    private Collection $employees;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\OneToMany(targetEntity: Visitor::class, mappedBy: 'destination')]
    private Collection $visitors;

    /**
     * @var Collection<int, Stakeholder>
     */
    #[ORM\OneToMany(targetEntity: Stakeholder::class, mappedBy: 'destination')]
    private Collection $stakeholders;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
        $this->visitors = new ArrayCollection();
        $this->stakeholders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(string $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getExtension(): ?int
    {
        return $this->extension;
    }

    public function setExtension(?int $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setAreaId($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getArea() === $this) {
                $employee->setArea(null);
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

    /**
     * @return Collection<int, Stakeholder>
     */
    public function getStakeholders(): Collection
    {
        return $this->stakeholders;
    }

    public function __toString(): string
    {
        return $this->unit ? sprintf('%s - %s', $this->building, $this->unit) : $this->building;
    }
}
