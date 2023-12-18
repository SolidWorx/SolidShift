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
use App\Repository\UserSiteAccessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserSiteAccessRepository::class)]
class UserSiteAccess
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'userAccess')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    #[ORM\Column(length: 15, enumType: UserRole::class)]
    private UserRole $role;

    public function __construct(?UserInterface $user = null, ?Site $site = null, ?UserRole $role = null)
    {
        if ($user instanceof User) {
            $this->setUser($user);
        }

        if ($role instanceof UserRole) {
            $this->setRole($role);
        }

        if ($site instanceof Site) {
            $this->setSite($site);
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
        if (! $user instanceof User) {
            unset($this->user);
        } else {
            $this->user = $user;
        }

        return $this;
    }

    public function getSite(): ?Site
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

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }
}
