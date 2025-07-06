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

use App\Entity\RecurringOptions as RecurringOptionsEntity;
use App\Enum\ScheduleEndType;
use App\Enum\ScheduleRecurringType;
use Carbon\CarbonImmutable;
use Carbon\WeekDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RecurringOptionsEntity>
 */
final class RecurringOptions extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'label' => 'Recurrence Type',
                'class' => ScheduleRecurringType::class,
                'required' => false,
            ])
            ->add('days', EnumType::class, [
                'class' => WeekDay::class,
                'multiple' => true,
                'expanded' => true,
                'help' => 'Select the days of the week the shift should recur on',
            ])
            ->add('endType', EnumType::class, [
                'label' => 'End Recurrence',
                'class' => ScheduleEndType::class,
                'choice_label' => static fn (ScheduleEndType $type) => $type->formLabel(),
                'expanded' => true,
                'required' => false,
                'help' => 'Select when the schedule should end',
            ])
            ->add(
                'endDate',
                DateType::class,
                [
                    'required' => false,
                    'input' => 'datetime_immutable',
                    'attr' => [
                        'min' => CarbonImmutable::now()
                            ->addDay()
                            ->format('Y-m-d'),
                    ],
                ]
            )
            ->add(
                'endOccurrence',
                NumberType::class,
                [
                    'attr' => ['min' => 0],
                    'html5' => true,
                    'empty_data' => '0',
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', RecurringOptionsEntity::class);
    }
}
