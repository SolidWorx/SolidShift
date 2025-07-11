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
    // #[Assert\GreaterThan('today')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    //#[Assert\GreaterThan(propertyPath: 'startTime')]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\OneToOne(mappedBy: 'schedule', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private ?RecurringOptions $recurringOptions = null;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\ManyToMany(targetEntity: Location::class, inversedBy: 'schedules', orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Assert\Valid]
    #[Assert\NotBlank]
    private Collection $locations;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    /**
     * @param Collection<int, Location>|null $locations
     */
    public function __construct(
        string $name = '',
        ?ScheduleType $scheduleType = null,
        ?Collection $locations = new ArrayCollection(),
        ?Site $site = null,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
        ?DateTimeImmutable $startTime = null,
        ?DateTimeImmutable $endTime = null,
        ?RecurringOptions $recurringOptions = null,
    ) {
        if ($scheduleType instanceof ScheduleType) {
            $this->scheduleType = $scheduleType;
        }

        $this->locations = $locations;

        if ($site instanceof Site) {
            $this->site = $site;
        }

        if ($startDate instanceof DateTimeInterface) {
            $this->startDate = $startDate;
        }

        if ($endDate instanceof DateTimeInterface) {
            $this->endDate = $endDate;
        }

        if ($startTime instanceof DateTimeInterface) {
            $this->startTime = $startTime;
        }

        if ($endTime instanceof DateTimeInterface) {
            $this->endTime = $endTime;
        }

        if ($recurringOptions instanceof RecurringOptions) {
            $this->recurringOptions = $recurringOptions;
        }

        $this->name = $name;
        $this->id = new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getStartDate(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->startDate);
    }

    public function setStartDate(DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate instanceof DateTimeInterface ? CarbonImmutable::instance($this->endDate) : null;
    }

    public function setEndDate(?DateTimeImmutable $endDate): static
    {
        if ($endDate instanceof DateTimeInterface) {
            $this->endDate = $endDate;
        }

        return $this;
    }

    public function getStartTime(): ?CarbonImmutable
    {
        return $this->startTime instanceof DateTimeInterface ? CarbonImmutable::instance($this->startTime) : null;
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?CarbonImmutable
    {
        return $this->endTime instanceof DateTimeInterface ? CarbonImmutable::instance($this->endTime) : null;
    }

    public function setEndTime(?DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

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
        if ($recurringOptions instanceof RecurringOptions && $this->scheduleType->isRecurring()) {
            $this->recurringOptions = $recurringOptions;
            $recurringOptions->setSchedule($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (! $this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        $this->locations->removeElement($location);

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

        if ($this->endTime instanceof DateTimeInterface) {
            return sprintf('%d %s to %s', $day, $this->startTime?->format('H:i'), $this->endTime->format('H:i'));
        }

        return sprintf('%s at %s', $day, $this->startTime?->format('H:i'));
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
}
