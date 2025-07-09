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
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ShiftRepository::class)]
class Shift
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Schedule $schedule;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Location $location;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $created;

    /**
     * @var Collection<int, ShiftAssignment>
     */
    #[ORM\OneToMany(mappedBy: 'shift', targetEntity: ShiftAssignment::class, orphanRemoval: true)]
    private Collection $assignments;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->created = new DateTimeImmutable();
        $this->assignments = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): static
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @return Collection<int, ShiftAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(ShiftAssignment $assignment): static
    {
        if (! $this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setShift($this);
        }

        return $this;
    }

    public function removeAssignment(ShiftAssignment $assignment): static
    {
        // set the owning side to null (unless already changed)
        if ($this->assignments->removeElement($assignment) && $assignment->getShift() === $this) {
            $assignment->setShift(null);
        }

        return $this;
    }
}
