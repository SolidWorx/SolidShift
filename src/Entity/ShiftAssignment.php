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

use App\Repository\ShiftAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ShiftAssignmentRepository::class)]
class ShiftAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private Shift $shift;

    #[ORM\ManyToOne(inversedBy: 'shifts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(
        ?Shift $shift = null,
        ?User $user = null,
    ) {
        if ($shift instanceof Shift) {
            $this->shift = $shift;
        }

        if ($user instanceof User) {
            $this->user = $user;
        }

        $this->id = new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function setShift(?Shift $shift): static
    {
        if ($shift instanceof Shift) {
            $this->shift = $shift;
        } else {
            unset($this->shift);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        if ($user instanceof User) {
            $this->user = $user;
        } else {
            unset($this->user);
        }

        return $this;
    }
}
