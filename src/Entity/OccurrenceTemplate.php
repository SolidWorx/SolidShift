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

use App\Repository\OccurrenceTemplateRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A named time-block within a Schedule that represents one distinct event in a
 * single day (e.g. "Morning Service", "Evening Service", "Lunch Sitting").
 *
 * A Schedule has 1..n OccurrenceTemplates. Each materialised date of the
 * Schedule produces one Occurrence per OccurrenceTemplate.
 */
#[ORM\Entity(repositoryClass: OccurrenceTemplateRepository::class)]
class OccurrenceTemplate implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $name = '';

    #[ORM\ManyToOne(inversedBy: 'occurrenceTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    private Schedule $schedule;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endTime = null;

    /**
     * @var Collection<int, ShiftRequirement>
     */
    #[ORM\OneToMany(mappedBy: 'occurrenceTemplate', targetEntity: ShiftRequirement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $requirements;

    public function __construct(
        string $name = '',
        ?Schedule $schedule = null,
        ?DateTimeImmutable $startTime = null,
        ?DateTimeImmutable $endTime = null,
    ) {
        $this->id = new Ulid();
        $this->name = $name;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->requirements = new ArrayCollection();

        if ($schedule instanceof Schedule) {
            $this->schedule = $schedule;
        }
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): static
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return Collection<int, ShiftRequirement>
     */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(ShiftRequirement $requirement): static
    {
        if (! $this->requirements->contains($requirement)) {
            $this->requirements->add($requirement);
            $requirement->setOccurrenceTemplate($this);
        }

        return $this;
    }

    public function removeRequirement(ShiftRequirement $requirement): static
    {
        $this->requirements->removeElement($requirement);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
