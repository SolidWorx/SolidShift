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

use App\Repository\UserAreaManagementRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserAreaManagementRepository::class)]
#[ORM\Table(name: 'user_area_management')]
#[ORM\UniqueConstraint(name: 'uniq_user_area', columns: ['user_id', 'area_id'])]
#[UniqueEntity(fields: ['user', 'area'], message: 'The user already manages this area')]
class UserAreaManagement
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'managedAreas')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Area $area;

    #[ORM\Column]
    private DateTimeImmutable $assignedAt;

    public function __construct(User $user, Area $area)
    {
        $this->id = new Ulid();
        $this->user = $user;
        $this->area = $area;
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

    public function getArea(): Area
    {
        return $this->area;
    }

    public function getAssignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }
}
