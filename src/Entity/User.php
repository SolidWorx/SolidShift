<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator\PhoneNumber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: User::TABLE_NAME)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['phone'], message: 'There is already an account with this phone number')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Stringable
{
    final public const TABLE_NAME = '`users`';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[Assert\NotBlank()]
    private string $username = '';

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password = '';

    /**
     * @var Collection<int, UserSiteAccess>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSiteAccess::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $siteAccess;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[PhoneNumber()]
    private ?string $phone = null;

    /**
     * @var Collection<int, UserInvite>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserInvite::class)]
    private Collection $invites;

    /**
     * @var Collection<int, Shift>
     */
    #[ORM\ManyToMany(targetEntity: Shift::class, mappedBy: 'users')]
    private Collection $shifts;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->siteAccess = new ArrayCollection();
        $this->invites = new ArrayCollection();
        $this->shifts = new ArrayCollection();
    }

    public function getId(): Ulid
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
        return $this->username;
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

    /**
     * @param list<string> $roles
     */
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
        if (! $this->siteAccess->contains($site)) {
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

    public function getFullName(): string
    {
        return trim(sprintf('%s %s', $this->firstName, $this->lastName));
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, UserInvite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(UserInvite $invite): static
    {
        if (! $this->invites->contains($invite)) {
            $this->invites->add($invite);
            $invite->setUser($this);
        }

        return $this;
    }

    public function removeInvite(UserInvite $invite): static
    {
        // set the owning side to null (unless already changed)
        if ($this->invites->removeElement($invite) && $invite->getUser() === $this) {
            $invite->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Shift>
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addSiteAccess(UserSiteAccess $siteAccess): static
    {
        if (! $this->siteAccess->contains($siteAccess)) {
            $this->siteAccess->add($siteAccess);
            $siteAccess->setUser($this);
        }

        return $this;
    }

    public function removeSiteAccess(UserSiteAccess $siteAccess): static
    {
        // set the owning side to null (unless already changed)
        if ($this->siteAccess->removeElement($siteAccess) && $siteAccess->getUser() === $this) {
            $siteAccess->setUser(null);
        }

        return $this;
    }

    public function addShift(Shift $shift): static
    {
        if (! $this->shifts->contains($shift)) {
            $this->shifts->add($shift);
            $shift->addUser($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): static
    {
        if ($this->shifts->removeElement($shift)) {
            $shift->removeUser($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
