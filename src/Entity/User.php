<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\ManyToOne(targetEntity: Application::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Application $application = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $prenom = null;

    /** Nom de l'application (si applicable pour le marchand) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameApplication = null;

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

    /**
     * @var Collection <int, Ticket>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Ticket::class, orphanRemoval: true)]
    private $tickets;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $profile = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'createdby')]
    private Collection $ticketsby;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->ticketsby = new ArrayCollection();
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNameApplication(): ?string
    {
        return $this->nameApplication;
    }

    public function setNameApplication(?string $nameApplication): static
    {
        $this->nameApplication = $nameApplication;

        return $this;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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

    public function getProfile(): ?Role
    {
        return $this->profile;
    }

    public function setProfile(?Role $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if ($this->profile?->getNom()) {
            $roles[] = $this->normalizeRoleName($this->profile->getNom());
        }

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($this->normalizeRoleName($role), $this->getRoles(), true);
    }

    private function normalizeRoleName(string $role): string
    {
        $role = strtoupper(trim($role));
        $role = str_replace([' ', '-'], '_', $role);

        return str_starts_with($role, 'ROLE_') ? $role : 'ROLE_' . $role;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setUser($this);
        }
        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            if ($ticket->getUser() === $this) {
                $ticket->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }



    /**
     * @return Collection<int, Ticket>
     */
    public function getTicketsby(): Collection
    {
        return $this->ticketsby;
    }

    public function addTicketsby(Ticket $ticketsby): static
    {
        if (!$this->ticketsby->contains($ticketsby)) {
            $this->ticketsby->add($ticketsby);
            $ticketsby->setCreatedby($this);
        }

        return $this;
    }

    public function removeTicketsby(Ticket $ticketsby): static
    {
        if ($this->ticketsby->removeElement($ticketsby)) {
            // set the owning side to null (unless already changed)
            if ($ticketsby->getCreatedby() === $this) {
                $ticketsby->setCreatedby(null);
            }
        }

        return $this;
    }
}
