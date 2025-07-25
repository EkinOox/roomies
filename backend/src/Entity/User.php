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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('email', message: 'Cet email est d�j� utilis�.')]
#[UniqueEntity('username', message: 'Ce nom d\'utilisateur est d�j� utilis�.')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  #[Groups(['user:read','room:read','game:read'])]

  private ?int $id = null;

  #[ORM\Column(length: 255, unique: true)]
  #[Groups(['user:read'])]
  #[Assert\NotBlank]
  #[Assert\Email]
  private ?string $email = null;

  #[ORM\Column(length: 255, unique: true)]
  #[Groups(['user:read', 'room:read','game:read'])]
  #[Assert\NotBlank]
  private ?string $username = null;

  #[ORM\Column(length: 255)]
  private ?string $password = null;

  #[ORM\Column]
  #[Groups(['user:read'])]
  private ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column(nullable: true)]
  #[Groups(['user:read'])]
  private ?\DateTimeImmutable $lastActive = null;

  #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Room::class)]
  private Collection $rooms;

  #[ORM\Column(length: 255)]
  #[Groups(['user:read', 'room:read', 'game:read'])]

  private ?string $avatar = null;

  /**
   * @var Collection<int, Game>
   */
  #[ORM\ManyToMany(targetEntity: Game::class)]
  #[Groups(['user:read', 'game:read'])]
  #[ORM\JoinTable(name: 'user_game')]
  private Collection $favoris;

  #[ORM\Column]
  #[Groups(['user:read'])]
  private array $roles = [];

  /**
   * @var Collection<int, Friendship>
   */
  #[ORM\OneToMany(mappedBy: 'user', targetEntity: Friendship::class)]
  private Collection $sentFriendRequests;

  /**
   * @var Collection<int, Friendship>
   */
  #[ORM\OneToMany(mappedBy: 'friend', targetEntity: Friendship::class)]
  private Collection $receivedFriendRequests;

  public function __construct()
  {
    $this->rooms = new ArrayCollection();
    $this->favoris = new ArrayCollection();
    $this->sentFriendRequests = new ArrayCollection();
    $this->receivedFriendRequests = new ArrayCollection();
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

  public function getLastActive(): ?\DateTimeImmutable
  {
    return $this->lastActive;
  }

  public function setLastActive(?\DateTimeImmutable $lastActive): static
  {
    $this->lastActive = $lastActive;
    return $this;
  }

  public function getRoles(): array
  {
    $roles = $this->roles;
    $roles[] = 'ROLE_USER'; // garantit que chaque utilisateur a au moins ce r�le
    return array_unique($roles);
  }

  public function eraseCredentials(): void
  {
    // Nettoyer les donn�es sensibles temporaires si besoin
  }

    public function getUserIdentifier(): string
    {
        return $this->email ?? "";
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

  public function setRoles(array $roles): static
  {
    $this->roles = $roles;

    return $this;
  }
  /**
   * @return Collection<int, Friendship>
   */
  public function getSentFriendRequests(): Collection
  {
    return $this->sentFriendRequests;
  }

  /**
   * @return Collection<int, Friendship>
   */
  public function getReceivedFriendRequests(): Collection
  {
    return $this->receivedFriendRequests;
  }
}
