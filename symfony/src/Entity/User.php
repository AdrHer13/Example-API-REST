<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'userId', targetEntity: WorkEntry::class)]
    private Collection $workEntries;

    public function __construct()
    {
        $this->workEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, WorkEntry>
     */
    public function getWorkEntries(): Collection
    {
        return $this->workEntries;
    }

    public function addWorkEntry(WorkEntry $workEntry): static
    {
        if (!$this->workEntries->contains($workEntry)) {
            $this->workEntries->add($workEntry);
            $workEntry->setUserId($this);
        }

        return $this;
    }

    public function removeWorkEntry(WorkEntry $workEntry): static
    {
        if ($this->workEntries->removeElement($workEntry)) {
            // set the owning side to null (unless already changed)
            if ($workEntry->getUserId() === $this) {
                $workEntry->setUserId(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now", new \DateTimeZone('Europe/Madrid'));
        $this->updatedAt = new \DateTime("now", new \DateTimeZone('Europe/Madrid'));
    }

    #[ORM\PreUpdate]
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now", new \DateTimeZone('Europe/Madrid'));
    }
}
