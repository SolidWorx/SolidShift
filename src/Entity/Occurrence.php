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

use App\Repository\OccurrenceRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * A materialised, dated instance of an OccurrenceTemplate. Created lazily when
 * the calendar is rendered or the first Shift is assigned for that date.
 */
#[ORM\Entity(repositoryClass: OccurrenceRepository::class)]
#[ORM\UniqueConstraint(columns: ['template_id', 'date'])]
class Occurrence
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private OccurrenceTemplate $template;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $date;

    /**
     * @var Collection<int, Shift>
     */
    #[ORM\OneToMany(mappedBy: 'occurrence', targetEntity: Shift::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $shifts;

    public function __construct(OccurrenceTemplate $template, DateTimeImmutable $date)
    {
        $this->id = new Ulid();
        $this->template = $template;
        $this->date = $date;
        $this->shifts = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTemplate(): OccurrenceTemplate
    {
        return $this->template;
    }

    public function getSchedule(): Schedule
    {
        return $this->template->getSchedule();
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Shift>
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(Shift $shift): static
    {
        if (! $this->shifts->contains($shift)) {
            $this->shifts->add($shift);
            $shift->setOccurrence($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): static
    {
        $this->shifts->removeElement($shift);

        return $this;
    }
}
