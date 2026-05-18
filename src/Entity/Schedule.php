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

use App\Enum\ScheduleType;
use App\Repository\ScheduleRepository;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 15, enumType: ScheduleType::class)]
    private ScheduleType $scheduleType;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank()]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\OneToOne(mappedBy: 'schedule', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private ?RecurringOptions $recurringOptions = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    /**
     * Time blocks within each occurrence of this Schedule (e.g. "Morning
     * Service" 09:00–11:00). A Schedule needs at least one to appear in the
     * calendar; form-level validation enforces this on submit.
     *
     * @var Collection<int, OccurrenceTemplate>
     */
    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: OccurrenceTemplate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $occurrenceTemplates;

    public function __construct(
        string $name = '',
        ?ScheduleType $scheduleType = null,
        ?Site $site = null,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
        ?RecurringOptions $recurringOptions = null,
    ) {
        if ($scheduleType instanceof ScheduleType) {
            $this->scheduleType = $scheduleType;
        }

        if ($site instanceof Site) {
            $this->site = $site;
        }

        if ($startDate instanceof DateTimeInterface) {
            $this->startDate = $startDate;
        }

        if ($endDate instanceof DateTimeInterface) {
            $this->endDate = $endDate;
        }

        if ($recurringOptions instanceof RecurringOptions) {
            $this->recurringOptions = $recurringOptions;
        }

        $this->name = $name;
        $this->id = new Ulid();
        $this->occurrenceTemplates = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getStartDate(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->startDate);
    }

    public function setStartDate(?DateTimeImmutable $startDate): static
    {
        if ($startDate instanceof DateTimeInterface) {
            $this->startDate = $startDate;
        }

        return $this;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate instanceof DateTimeInterface ? CarbonImmutable::instance($this->endDate) : null;
    }

    public function setEndDate(?DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isRecurring(): bool
    {
        return $this->scheduleType->isRecurring() && $this->recurringOptions instanceof RecurringOptions;
    }

    public function getRecurringOptions(): ?RecurringOptions
    {
        return $this->recurringOptions;
    }

    public function setRecurringOptions(?RecurringOptions $recurringOptions): static
    {
        if ($recurringOptions instanceof RecurringOptions && isset($this->scheduleType) && $this->scheduleType->isRecurring()) {
            $this->recurringOptions = $recurringOptions;
            $recurringOptions->setSchedule($this);
        }

        return $this;
    }

    public function getScheduleType(): ScheduleType
    {
        return $this->scheduleType;
    }

    public function setScheduleType(ScheduleType $scheduleType): void
    {
        $this->scheduleType = $scheduleType;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): void
    {
        if ($site instanceof Site) {
            $this->site = $site;
        } else {
            unset($this->site);
        }
    }

    public function __toString(): string
    {
        if ($this->recurringOptions instanceof RecurringOptions) {
            return (string) $this->recurringOptions;
        }

        $day = $this->startDate->format('d F Y');

        if ($this->endDate instanceof DateTimeInterface) {
            return sprintf('%s to %s', $day, $this->endDate->format('d F Y'));
        }

        return $day;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, OccurrenceTemplate>
     */
    public function getOccurrenceTemplates(): Collection
    {
        return $this->occurrenceTemplates;
    }

    public function addOccurrenceTemplate(OccurrenceTemplate $occurrenceTemplate): static
    {
        if (! $this->occurrenceTemplates->contains($occurrenceTemplate)) {
            $this->occurrenceTemplates->add($occurrenceTemplate);
            $occurrenceTemplate->setSchedule($this);
        }

        return $this;
    }

    public function removeOccurrenceTemplate(OccurrenceTemplate $occurrenceTemplate): static
    {
        $this->occurrenceTemplates->removeElement($occurrenceTemplate);

        return $this;
    }
}
