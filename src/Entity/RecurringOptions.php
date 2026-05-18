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

use App\Enum\ScheduleEndType;
use App\Enum\ScheduleRecurringType;
use App\Repository\RecurringOptionsRepository;
use Carbon\CarbonImmutable;
use Carbon\Unit;
use Carbon\WeekDay;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Arr;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function in_array;
use function sprintf;

#[ORM\Entity(repositoryClass: RecurringOptionsRepository::class)]
#[Assert\Callback(callback: 'validateDays')]
class RecurringOptions implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 15, enumType: ScheduleRecurringType::class)]
    private ScheduleRecurringType $type;

    /**
     * @var list<int>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $days = [];

    #[ORM\Column(length: 15, enumType: ScheduleEndType::class)]
    private ScheduleEndType $endType;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(value: 'today', message: 'End date must be in the future')]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $endOccurrence = null;

    #[ORM\OneToOne(inversedBy: 'recurringOptions', targetEntity: Schedule::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Schedule $schedule;

    /**
     * @param list<int> $days
     */
    public function __construct(
        ?ScheduleRecurringType $type = null,
        ?ScheduleEndType $endType = null,
        ?DateTimeImmutable $endDate = null,
        array $days = [],
        ?int $endOccurrence = null
    ) {
        if ($type instanceof ScheduleRecurringType) {
            $this->setType($type);
        }

        if ($endType instanceof ScheduleEndType) {
            $this->setEndType($endType);
        }

        $this->setEndDate($endDate);
        $this->setEndOccurrence($endOccurrence);
        $this->setDays($days);
        $this->id = new Ulid();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getType(): ScheduleRecurringType
    {
        return $this->type;
    }

    public function setType(ScheduleRecurringType $type): static
    {
        $this->type = $type;

        if ($this->type->isDaily()) {
            $this->days = [];
        }

        return $this;
    }

    /**
     * @return list<WeekDay>
     */
    public function getDays(): array
    {
        return array_map(WeekDay::from(...), $this->days);
    }

    /**
     * @param list<int> $days
     */
    public function setDays(array $days): static
    {
        if ([] !== $days && $this->type->isWeekly()) {
            $this->days = $days;
        }

        return $this;
    }

    public function getEndType(): ScheduleEndType
    {
        return $this->endType;
    }

    public function setEndType(ScheduleEndType $endType): static
    {
        $this->endType = $endType;

        return $this;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate instanceof DateTimeInterface ? CarbonImmutable::instance($this->endDate) : null;
    }

    public function setEndDate(?DateTimeImmutable $endDate): static
    {
        if ($endDate instanceof DateTimeImmutable) {
            $this->endDate = $endDate;
        }

        return $this;
    }

    public function getEndOccurrence(): ?int
    {
        return $this->endOccurrence;
    }

    public function setEndOccurrence(?int $endOccurrence): static
    {
        $this->endOccurrence = $endOccurrence;

        return $this;
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function validateDays(ExecutionContextInterface $context): void
    {
        if (! isset($this->type)) {
            $context->buildViolation('You must select a recurrence type')
                ->atPath('type')
                ->addViolation();
            return;
        }

        if ([] === $this->days && $this->type->isWeekly()) {
            $context->buildViolation('You must select at least one day for daily recurrence')
                ->atPath('days')
                ->addViolation();
        }

        if (! isset($this->endType)) {
            $context->buildViolation('You must select an end type')
                ->atPath('endType')
                ->addViolation();
            return;
        }

        if ((0 === $this->endOccurrence || null === $this->endOccurrence) && $this->endType->isAfter()) {
            $context->buildViolation('You must specify the number of occurrences')
                ->atPath('endOccurrence')
                ->addViolation();
        }

        if (! $this->endDate instanceof DateTimeInterface && $this->endType->isOn()) {
            $context->buildViolation('You must specify an end date')
                ->atPath('endDate')
                ->addViolation();
        }
    }

    public function __toString(): string
    {
        if ($this->type->isWeekly()) {
            $string = sprintf('Every %s', Arr::join(array_map(static fn (WeekDay $day) => $day->name, $this->getDays()), ', ', ' and '));
        } else {
            $string = 'Every day';
        }

        if ($this->endType->isOn()) {
            $string .= sprintf(' from %s to %s', $this->schedule->getStartDate()->format('d F Y'), $this->endDate?->format('d F Y'));
        } elseif ($this->endType->isAfter()) {
            $totalOccurrence = 0;
            $start = CarbonImmutable::instance($this->schedule->getStartDate());

            $dates = $start->range(null, Unit::Day->interval());
            $endDate = null;

            foreach ($dates->getIterator() as $date) {
                /** @var CarbonImmutable $date */
                if (in_array($date->dayOfWeek, $this->days, true)) {
                    $totalOccurrence++;

                    if ($totalOccurrence === $this->endOccurrence) {
                        $endDate = $date;
                        break;
                    }
                }
            }

            $string .= sprintf(' from %s to %s (%d occurrences)', $this->schedule->getStartDate()->format('d F Y'), $endDate?->format('d F Y'), $this->endOccurrence);
        } else {
            $string .= sprintf(' from %s', $this->schedule->getStartDate()->format('d F Y'));
        }

        return $string;
    }
}
