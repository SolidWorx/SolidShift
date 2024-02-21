<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Components;

use App\Entity\Shift;
use App\Entity\User;
use App\Form\UserAutocompleteType;
use App\Model\ScheduleDate;
use App\Repository\ShiftRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ShiftUsers extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[ExposeInTemplate]
    #[LiveProp(writable: true)]
    private ?ScheduleDate $scheduleDate = null;

    public function __construct(
        private readonly ShiftRepository $shiftRepository
    ) {
    }

    public function mount(ScheduleDate $shiftDate): void
    {
        $this->setScheduleDate($shiftDate);
    }

    public function getScheduleDate(): ?ScheduleDate
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(?ScheduleDate $scheduleDate): self
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    #[ExposeInTemplate()]
    public function getShift(): ?Shift
    {
        return $this->shiftRepository->findOneBy([
            'schedule' => $this->scheduleDate?->schedule,
            'startDate' => $this->scheduleDate?->startDate,
            'startTime' => $this->scheduleDate?->startTime,
            'endDate' => $this->scheduleDate?->endDate,
            'endTime' => $this->scheduleDate?->endTime,
        ]);
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add(
                'users',
                UserAutocompleteType::class,
                [
                    'extra_options' => [
                        'exclude_users' => $this->getShift()?->getUsers()->map(static fn (User $user) => $user->getId()->toBase32())->toArray(),
                    ],
                ]
            )
            ->getForm()
        ;
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        if (! $this->scheduleDate instanceof ScheduleDate) {
            throw new \LogicException('No schedule date set');
        }

        /** @var array{users: Collection<int, User>} $data */
        $data = $this->getForm()->getData();

        $shift = $this->getShift();

        if (! $shift instanceof Shift) {
            $shift = (new Shift())
                ->setStartDate($this->scheduleDate->getStartDate())
                ->setEndDate($this->scheduleDate->endDate)
                ->setStartTime($this->scheduleDate->startTime)
                ->setEndTime($this->scheduleDate->endTime)
                ->setLocation($this->scheduleDate->getSchedule()->getLocation())
                ->setSchedule($this->scheduleDate->schedule)
            ;
        }

        foreach ($data['users'] ?? [] as $user) {
            $user->addShift($shift);
        }

        $entityManager->persist($shift);
        $entityManager->flush();

        $this->dispatchBrowserEvent('modal:close:' . $this->scheduleDate->getHash());
        $this->resetForm();
    }

    #[LiveAction]
    public function removeUser(#[LiveArg] User $user, #[LiveArg] Shift $shift, EntityManagerInterface $entityManager): void
    {
        $user->removeShift($shift);
        $entityManager->flush();
    }
}
