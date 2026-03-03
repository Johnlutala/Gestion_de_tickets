<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "tb_user")]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $tickets;

    #[ORM\ManyToOne(inversedBy: 'user_id')]
    private ?Role $profile = null;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

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
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicketId(Ticket $ticketId): static
    {
        if (!$this->tickets->contains($ticketId)) {
            $this->tickets->add($ticketId);
            $ticketId->setUser($this);
        }

        return $this;
    }

    public function removeTicketId(Ticket $ticketId): static
    {
        if ($this->tickets->removeElement($ticketId)) {
            // set the owning side to null (unless already changed)
            if ($ticketId->getUser() === $this) {
                $ticketId->setUser(null);
            }
        }

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

    public function getRoles(): array {
        $roles = ['ROLE_USER'];

        $privileges = $this->profile->getPrivileges();
        foreach ($privileges as $privilege) {
            $roles[] = 'ROLE_' . strtoupper($privilege->getName());
        }

        return $roles;
    }

}
