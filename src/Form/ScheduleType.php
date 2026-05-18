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

use App\Entity\Organisation;
use App\Entity\Schedule;
use App\Entity\Site;
use App\Enum\ScheduleType as ScheduleTypeEnum;
use Carbon\CarbonImmutable;
use Override;
use Psr\Clock\ClockInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

/**
 * @extends AbstractType<Schedule>
 */
final class ScheduleType extends AbstractType
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $minStartDate = $options['edit'] === false ? [
            'min' => CarbonImmutable::instance($this->clock->now())->addDay()->format('Y-m-d'),
        ] : [];

        $builder
            ->add('name', null, ['empty_data' => ''])
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
                    'label' => 'Start Date',
                    'attr' => $minStartDate,
                    'input' => 'datetime_immutable',
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'label' => 'End Date',
                    'required' => false,
                    'input' => 'datetime_immutable',
                    'help' => 'Leave blank for an open-ended schedule',
                ]
            )
            ->add('recurringOptions', RecurringOptions::class, ['required' => false])
            ->add('occurrenceTemplates', LiveCollectionType::class, [
                'label' => 'Sub-events',
                'help' => 'Each schedule has at least one sub-event (e.g. "Morning Service"). Add staffing requirements under each.',
                'entry_type' => OccurrenceTemplateType::class,
                'entry_options' => [
                    'label' => false,
                    'site' => $options['site'],
                    'organisation' => $options['organisation'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Assert\Count(min: 1, minMessage: 'Add at least one sub-event')],
                'button_add_options' => [
                    'label' => 'Add sub-event',
                    'attr' => ['class' => 'btn btn-sm btn-secondary'],
                ],
                'button_delete_options' => [
                    'label' => 'Remove sub-event',
                    'attr' => ['class' => 'btn btn-sm btn-outline-danger'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Schedule::class);
        $resolver->setDefault('edit', false);
        $resolver->setAllowedTypes('edit', 'bool');
        $resolver->setRequired(['site', 'organisation']);
        $resolver->setAllowedTypes('site', Site::class);
        $resolver->setAllowedTypes('organisation', Organisation::class);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'schedule';
    }
}
