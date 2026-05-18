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

use App\Repository\UserSiteRoleAssignmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserSiteRoleAssignmentRepository::class)]
#[ORM\Table(name: 'user_site_role')]
#[ORM\UniqueConstraint(name: 'uniq_user_site_role', columns: ['user_id', 'site_id', 'role_id'])]
#[UniqueEntity(fields: ['user', 'site', 'role'], message: 'This role is already assigned to the user for this site')]
class UserSiteRoleAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'roleAssignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Site $site;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\Column]
    private DateTimeImmutable $assignedAt;

    public function __construct(User $user, Site $site, Role $role)
    {
        if ($role->getOrganisation() !== $site->getOrganisation()) {
            throw new InvalidArgumentException('Role and Site must belong to the same Organisation.');
        }

        $this->id = new Ulid();
        $this->user = $user;
        $this->site = $site;
        $this->role = $role;
        $this->assignedAt = new DateTimeImmutable();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getAssignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }
}
