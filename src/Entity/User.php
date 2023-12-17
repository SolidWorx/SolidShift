<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: User::TABLE_NAME)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const TABLE_NAME = '`users`';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[Assert\NotBlank()]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSiteAccess::class, orphanRemoval: true)]
    private Collection $siteAccess;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->siteAccess = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, UserSiteAccess>
     */
    public function getSiteAccess(): Collection
    {
        return $this->siteAccess;
    }

    public function addSite(UserSiteAccess $site): static
    {
        if (!$this->siteAccess->contains($site)) {
            $this->siteAccess->add($site);
            $site->setUser($this);
        }

        return $this;
    }

    public function removeSite(UserSiteAccess $site): static
    {
        // set the owning side to null (unless already changed)
        if ($this->siteAccess->removeElement($site) && $site->getUser() === $this) {
            $site->setUser(null);
        }

        return $this;
    }
}
