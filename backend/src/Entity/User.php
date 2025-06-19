<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read'])]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Room::class)]
    private Collection $rooms;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $avatar = null;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\ManyToMany(targetEntity: Game::class)]
    #[Groups(['user:read'])]
    private Collection $favoris;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'friends')]
    #[ORM\JoinTable(name: 'user_friends',
        joinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'friend_id', referencedColumnName: 'id')]
    )]
    #[Groups(['user:read'])]
    private Collection $friends;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->friends = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Nettoyer les données sensibles temporaires si besoin
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getSalt(): ?string
    {
        // Non utilisé avec bcrypt ou argon2i
        return null;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Game $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
        }
        return $this;
    }

    public function removeFavori(Game $favori): static
    {
        $this->favoris->removeElement($favori);
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(self $friend): static
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
        }
        return $this;
    }

    public function removeFriend(self $friend): static
    {
        $this->friends->removeElement($friend);
        return $this;
    }
}
