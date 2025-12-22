<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'checkInUser')]
    private Collection $attendancesCheckIn;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'checkOutUser')]
    private Collection $attendancesCheckOut;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\OneToMany(targetEntity: Visitor::class, mappedBy: 'checkInUser')]
    private Collection $visitorsCheckIn;

    /**
     * @var Collection<int, Visitor>
     */
    #[ORM\OneToMany(targetEntity: Visitor::class, mappedBy: 'checkOutUser')]
    private Collection $visitorsCheckOut;

    public function __construct()
    {
        $this->attendancesCheckIn = new ArrayCollection();
        $this->attendancesCheckOut = new ArrayCollection();
        $this->visitorsCheckIn = new ArrayCollection();
        $this->visitorsCheckOut = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendancesCheckIn(): Collection
    {
        return $this->attendancesCheckIn;
    }

    public function addAttendancesCheckIn(Attendance $attendancesCheckIn): static
    {
        if (!$this->attendancesCheckIn->contains($attendancesCheckIn)) {
            $this->attendancesCheckIn->add($attendancesCheckIn);
            $attendancesCheckIn->setCheckInUser($this);
        }

        return $this;
    }

    public function removeAttendancesCheckIn(Attendance $attendancesCheckIn): static
    {
        if ($this->attendancesCheckIn->removeElement($attendancesCheckIn)) {
            // set the owning side to null (unless already changed)
            if ($attendancesCheckIn->getCheckInUser() === $this) {
                $attendancesCheckIn->setCheckInUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendancesCheckOut(): Collection
    {
        return $this->attendancesCheckOut;
    }

    public function addAttendancesCheckOut(Attendance $attendancesCheckOut): static
    {
        if (!$this->attendancesCheckOut->contains($attendancesCheckOut)) {
            $this->attendancesCheckOut->add($attendancesCheckOut);
            $attendancesCheckOut->setCheckOutUser($this);
        }

        return $this;
    }

    public function removeAttendancesCheckOut(Attendance $attendancesCheckOut): static
    {
        if ($this->attendancesCheckOut->removeElement($attendancesCheckOut)) {
            // set the owning side to null (unless already changed)
            if ($attendancesCheckOut->getCheckOutUser() === $this) {
                $attendancesCheckOut->setCheckOutUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Visitor>
     */
    public function getVisitorsCheckIn(): Collection
    {
        return $this->visitorsCheckIn;
    }

    public function addVisitorsCheckIn(Visitor $visitorsCheckIn): static
    {
        if (!$this->visitorsCheckIn->contains($visitorsCheckIn)) {
            $this->visitorsCheckIn->add($visitorsCheckIn);
            $visitorsCheckIn->setCheckInUser($this);
        }

        return $this;
    }

    public function removeVisitorsCheckIn(Visitor $visitorsCheckIn): static
    {
        if ($this->visitorsCheckIn->removeElement($visitorsCheckIn)) {
            // set the owning side to null (unless already changed)
            if ($visitorsCheckIn->getCheckInUser() === $this) {
                $visitorsCheckIn->setCheckInUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Visitor>
     */
    public function getVisitorsCheckOut(): Collection
    {
        return $this->visitorsCheckOut;
    }

    public function addVisitorsCheckOut(Visitor $visitorsCheckOut): static
    {
        if (!$this->visitorsCheckOut->contains($visitorsCheckOut)) {
            $this->visitorsCheckOut->add($visitorsCheckOut);
            $visitorsCheckOut->setCheckOutUser($this);
        }

        return $this;
    }

    public function removeVisitorsCheckOut(Visitor $visitorsCheckOut): static
    {
        if ($this->visitorsCheckOut->removeElement($visitorsCheckOut)) {
            // set the owning side to null (unless already changed)
            if ($visitorsCheckOut->getCheckOutUser() === $this) {
                $visitorsCheckOut->setCheckOutUser(null);
            }
        }

        return $this;
    }
}
