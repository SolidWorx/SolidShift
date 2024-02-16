<?php

namespace App\Components;

use App\Entity\Location;
use App\Model\ScheduleDate;
use App\Repository\ScheduleRepository;
use Illuminate\Support\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function collect;
use function in_array;

#[AsLiveComponent(
    template: 'components/shift/upcoming.html.twig',
)]
final class UpcomingShifts
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    /**
     * @var array<int, string>
     */
    #[LiveProp(writable: true, url: true)]
    public array $query = [];

    public function __construct(
        private readonly ScheduleRepository   $scheduleRepository,
        private readonly FormFactoryInterface $formFactory,
    ) {}

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->add(
                'location',
                EntityType::class,
                [
                    'class' => Location::class,
                    'required' => false,
                    'choice_attr' => static fn(Location $location): array => ['data-live-id' => (string) $location->getId()],
                ]
            )
            ->getForm();
    }

    /**
     * @return Collection<int, Collection<int, ScheduleDate>>
     */
    public function getShiftDates(): Collection
    {
        $schedules = collect($this->scheduleRepository->getScheduleListForActiveSchedules()->getSortedScheduledDates());

        if ($this->query !== []) {
            $schedules = $schedules->filter(
                fn (ScheduleDate $schedule) => in_array($schedule->schedule?->getLocation()->getId()->toBase32(), $this->query, true)
            );
        }

        return $schedules
            ->groupBy(static fn (ScheduleDate $schedule) => (int) $schedule->startDate?->timestamp);
    }
}
