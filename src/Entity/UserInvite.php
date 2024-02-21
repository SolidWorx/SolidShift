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

use App\Enum\UserRole;
use App\Repository\UserInviteRepository;
use App\Validator\PhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserInviteRepository::class)]
#[ORM\Index(fields: ['hash'])]
#[ORM\UniqueConstraint(fields: ['hash'])]
#[UniqueEntity(fields: ['site', 'email'], message: 'An invitation for the provided email already exists')]
#[UniqueEntity(fields: ['site', 'phone'], message: 'An invitation for the provided phone already exists')]
class UserInvite
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'invites')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'invites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\Valid()]
    private Site $site;

    #[ORM\Column(length: 125, unique: true)]
    private string $hash = '';

    #[ORM\Column(length: 125, nullable: true)]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[PhoneNumber()]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 25, enumType: UserRole::class)]
    private UserRole $role;

    public function __construct(
        ?User $user = null,
        ?Site $site = null,
        UserRole $role = UserRole::ROLE_USER,
        ?string $email = null,
        ?string $phone = null,
    ) {
        $this->user = $user;
        $this->email = $email;
        $this->phone = $phone;
        $this->role = $role;

        if ($site instanceof Site) {
            $this->site = $site;
        }

        $this->id = new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
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

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        if (! $site instanceof Site) {
            unset($this->site);
        } else {
            $this->site = $site;
        }

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }
}
