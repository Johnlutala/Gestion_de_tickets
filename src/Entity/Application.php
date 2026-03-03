<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: "tb_application")]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $key_id = null;

    #[ORM\Column(length: 255)]
    private ?string $secret_key = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'application_id', orphanRemoval: true)]
    private Collection $ticket_id;

    public function __construct()
    {
        $this->ticket_id = new ArrayCollection();
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

    public function getKeyId(): ?int
    {
        return $this->key_id;
    }

    public function setKeyId(int $key_id): static
    {
        $this->key_id = $key_id;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secret_key;
    }

    public function setSecretKey(string $secret_key): static
    {
        $this->secret_key = $secret_key;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTicketId(): Collection
    {
        return $this->ticket_id;
    }

    public function addTicketId(Ticket $ticketId): static
    {
        if (!$this->ticket_id->contains($ticketId)) {
            $this->ticket_id->add($ticketId);
            $ticketId->setApplicationId($this);
        }

        return $this;
    }

    public function removeTicketId(Ticket $ticketId): static
    {
        if ($this->ticket_id->removeElement($ticketId)) {
            // set the owning side to null (unless already changed)
            if ($ticketId->getApplicationId() === $this) {
                $ticketId->setApplicationId(null);
            }
        }

        return $this;
    }
}
