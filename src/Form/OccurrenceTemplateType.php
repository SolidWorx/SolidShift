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

use App\Entity\OccurrenceTemplate;
use App\Entity\Organisation;
use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

/**
 * @extends AbstractType<OccurrenceTemplate>
 */
final class OccurrenceTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'help' => 'e.g. "Morning Service" or "Lunch Sitting"',
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('endTime', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'help' => 'Leave blank for an open-ended block',
            ])
            ->add('requirements', LiveCollectionType::class, [
                'label' => 'Staffing requirements',
                'entry_type' => ShiftRequirementType::class,
                'entry_options' => [
                    'label' => false,
                    'site' => $options['site'],
                    'organisation' => $options['organisation'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'button_add_options' => [
                    'label' => 'Add staffing requirement',
                    'attr' => ['class' => 'btn btn-sm btn-secondary'],
                ],
                'button_delete_options' => [
                    'label' => 'Remove',
                    'attr' => ['class' => 'btn btn-sm btn-outline-danger'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => OccurrenceTemplate::class]);
        $resolver->setRequired(['site', 'organisation']);
        $resolver->setAllowedTypes('site', Site::class);
        $resolver->setAllowedTypes('organisation', Organisation::class);
    }
}
