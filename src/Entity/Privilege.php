<?php

namespace App\Entity;

use App\Repository\PrivilegeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrivilegeRepository::class)]
#[ORM\Table(name: "tb_privilege")]
class Privilege
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'privilege')]
    private Collection $role_id;

    public function __construct()
    {
        $this->role_id = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleId(): Collection
    {
        return $this->role_id;
    }

    public function addRoleId(Role $roleId): static
    {
        if (!$this->role_id->contains($roleId)) {
            $this->role_id->add($roleId);
            $roleId->addPrivilege($this);
        }

        return $this;
    }

    public function removeRoleId(Role $roleId): static
    {
        if ($this->role_id->removeElement($roleId)) {
            $roleId->removePrivilege($this);
        }

        return $this;
    }
}
