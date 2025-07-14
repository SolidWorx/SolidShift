<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Schedule;
use App\Enum\ScheduleType as ScheduleTypeEnum;
use Carbon\CarbonImmutable;
use Override;
use Psr\Clock\ClockInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Schedule>
 */
final class ScheduleType extends AbstractType
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $minStartDate = $options['edit'] === false ? [
            'min' => CarbonImmutable::instance($this->clock->now())->addDay()->format('Y-m-d'),
        ] : [];

        $builder
            ->add('name')
            ->add(
                'scheduleType',
                EnumType::class,
                [
                    'class' => ScheduleTypeEnum::class,
                    'expanded' => true,
                ]
            )
            ->add(
                'startDate',
                DateType::class,
                [
                    'label' => 'Shift Date',
                    'attr' => $minStartDate,
                    'input' => 'datetime_immutable',
                ]
            )
            ->add('startTime', TimeType::class)
            ->add('endTime', TimeType::class, ['required' => false, 'help' => 'Leave blank if the shift has no end time'])
            ->add('recurringOptions', RecurringOptions::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Schedule::class);
        $resolver->setDefault('edit', false);
        $resolver->setAllowedTypes('edit', 'bool');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'schedule';
    }
}
