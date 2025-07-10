<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[UniqueEntity('name', message: 'Ce nom de room est déjà utilisé.')]
class Room
{

    #[ORM\Id] // Indique que ce champ est la clé primaire (Primary Key) de l'entité
    #[ORM\GeneratedValue]// Précise que la valeur de ce champ est générée automatiquement (auto-incrémentée)
    #[ORM\Column]// Définit ce champ comme une colonne de la base de données (type auto-détecté, généralement INTEGER pour une ID)
    #[Groups(['room:read'])]  // Spécifie que ce champ doit être exposé dans la sérialisation quand on utilise le groupe 'room:read' / Utile par exemple dans les API JSON, pour contrôler quels champs sont visibles dans chaque contexte
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['room:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['room:read'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Groups(['room:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['room:read'])]
    private ?string $gameType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['room:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Groups(['room:read'])]
    private ?string $status = 'waiting';

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rooms')]
    #[Groups(['room:read'])]
    private $owner;

    #[ORM\Column(type: 'integer')]
    #[Groups(['room:read'])]
    private ?int $maxPlayers = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['room:read'])]
    private ?Game $game = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[Groups(['room:read'])]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getGameType(): ?string
    {
        return $this->gameType;
    }

    public function setGameType(string $gameType): static
    {
        $this->gameType = $gameType;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPlayers(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
    public function getMaxPlayers(): ?int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $maxPlayers): self
    {
        $this->maxPlayers = $maxPlayers;
        return $this;
    }
}
