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

use App\Repository\ShiftRepository;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * Concrete assignment of a user to a staffing slot on a specific date.
 *
 * Required references:
 *   - Occurrence    — the dated instance this shift is part of
 *   - ShiftRequirement — the slot being filled (so we know role/area defaults)
 *   - User          — who is working
 */
#[ORM\Entity(repositoryClass: ShiftRepository::class)]
class Shift
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'shifts')]
    #[ORM\JoinColumn(nullable: false)]
    private Occurrence $occurrence;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ShiftRequirement $requirement;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Role $role;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Area $area = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $created;

    #[ORM\ManyToOne(inversedBy: 'shifts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(
        ?Occurrence $occurrence = null,
        ?ShiftRequirement $requirement = null,
        ?User $user = null,
    ) {
        $this->id = new Ulid();
        $this->created = CarbonImmutable::now();

        if ($occurrence instanceof Occurrence) {
            $this->occurrence = $occurrence;
        }

        if ($requirement instanceof ShiftRequirement) {
            $this->requirement = $requirement;
            $this->role = $requirement->getRole();
            $this->area = $requirement->getArea();
        }

        if ($user instanceof User) {
            $this->user = $user;
        }
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getOccurrence(): Occurrence
    {
        return $this->occurrence;
    }

    public function setOccurrence(Occurrence $occurrence): static
    {
        $this->occurrence = $occurrence;

        return $this;
    }

    public function getRequirement(): ShiftRequirement
    {
        return $this->requirement;
    }

    public function setRequirement(ShiftRequirement $requirement): static
    {
        $this->requirement = $requirement;

        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->occurrence->getDate();
    }

    public function getSchedule(): Schedule
    {
        return $this->occurrence->getSchedule();
    }

    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime ?? $this->requirement->getStartTime();
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime ?? $this->requirement->getEndTime();
    }

    public function setEndTime(?DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
