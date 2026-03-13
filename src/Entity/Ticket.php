<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: "tb_ticket")]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;


    #[ORM\Column(length: 50)]
    private ?string $marchand = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\Column]
    private ?bool $noted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Application $application = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column]
    private ?bool $deleted = null;

    #[ORM\ManyToOne(inversedBy: 'ticketsby')]
    private ?User $createdby = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(nullable: true)]
    private ?int $month = null;

    #[ORM\Column(nullable: true)]
    private ?int $quarter = null;

    // ───── Chat / Conversation tree ─────

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?self $parent = null;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $replies;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachmentPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachmentOriginalName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $attachmentMimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $attachmentSize = null;

    public function __construct()
    {
        $this->replies   = new ArrayCollection();
        $this->enabled   = true;
        $this->deleted   = false;
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    private function syncPeriodFromCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->year = null;
            $this->month = null;
            $this->quarter = null;

            return;
        }

        $month = (int) $this->createdAt->format('n');

        $this->year = (int) $this->createdAt->format('Y');
        $this->month = $month;
        $this->quarter = (int) floor(($month - 1) / 3) + 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getMarchand(): ?string
    {
        return $this->marchand;
    }

    public function setMarchand(string $marchand): static
    {
        $this->marchand = $marchand;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function isNoted(): ?bool
    {
        return $this->noted;
    }

    public function setNoted(bool $noted): static
    {
        $this->noted = $noted;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCreatedby(): ?User
    {
        return $this->createdby;
    }

    public function setCreatedby(?User $createdby): static
    {
        $this->createdby = $createdby;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter;
    }

    public function setQuarter(?int $quarter): static
    {
        $this->quarter = $quarter;

        return $this;
    }

    // ───── Chat accessors ─────

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /** @return Collection<int, Ticket> */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setParent($this);
        }

        return $this;
    }

    public function removeReply(self $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        $this->syncPeriodFromCreatedAt();

        return $this;
    }

    public function getAttachmentPath(): ?string
    {
        return $this->attachmentPath;
    }

    public function setAttachmentPath(?string $attachmentPath): static
    {
        $this->attachmentPath = $attachmentPath;

        return $this;
    }

    public function getAttachmentOriginalName(): ?string
    {
        return $this->attachmentOriginalName;
    }

    public function setAttachmentOriginalName(?string $attachmentOriginalName): static
    {
        $this->attachmentOriginalName = $attachmentOriginalName;

        return $this;
    }

    public function getAttachmentMimeType(): ?string
    {
        return $this->attachmentMimeType;
    }

    public function setAttachmentMimeType(?string $attachmentMimeType): static
    {
        $this->attachmentMimeType = $attachmentMimeType;

        return $this;
    }

    public function getAttachmentSize(): ?int
    {
        return $this->attachmentSize;
    }

    public function setAttachmentSize(?int $attachmentSize): static
    {
        $this->attachmentSize = $attachmentSize;

        return $this;
    }

    public function hasAttachment(): bool
    {
        return $this->attachmentPath !== null && $this->attachmentPath !== '';
    }

    public function isImageAttachment(): bool
    {
        return $this->attachmentMimeType !== null && str_starts_with($this->attachmentMimeType, 'image/');
    }

    /** Renvoie true si ce ticket est une conversation racine (pas une réponse). */
    public function isRootTicket(): bool
    {
        return $this->parent === null;
    }
}
