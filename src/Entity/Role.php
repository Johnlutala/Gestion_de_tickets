<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: "tb_role")]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'profile')]
    private Collection $users;

    /**
     * @var Collection<int, Privilege>
     */
    #[ORM\ManyToMany(targetEntity: Privilege::class, inversedBy: 'roles')]
    #[ORM\JoinTable(name: 'role_privilege')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'privilege_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $privileges;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->privileges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        // Auto-prefix with ROLE_ if not already present
        if (!str_starts_with($nom, 'ROLE_')) {
            $nom = 'ROLE_' . $nom;
        }
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUserId(User $userId): static
    {
        if (!$this->users->contains($userId)) {
            $this->users->add($userId);
            $userId->setProfile($this);
        }

        return $this;
    }

    public function removeUserId(User $userId): static
    {
        if ($this->users->removeElement($userId)) {
            // set the owning side to null (unless already changed)
            if ($userId->getProfile() === $this) {
                $userId->setProfile(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Privilege>
     */
    public function getPrivileges(): Collection
    {
        return $this->privileges;
    }

    public function addPrivilege(Privilege $privilege): static
    {
        if (!$this->privileges->contains($privilege)) {
            $this->privileges->add($privilege);
        }

        return $this;
    }

    public function removePrivilege(Privilege $privilege): static
    {
        $this->privileges->removeElement($privilege);

        return $this;
    }
}
