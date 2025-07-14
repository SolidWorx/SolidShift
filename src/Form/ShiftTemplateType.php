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

use App\Entity\Location;
use App\Entity\Position;
use App\Entity\ShiftTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ShiftTemplate>
 */
final class ShiftTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => true,
        ]);

        $builder->add('description', TextareaType::class, [
            'required' => false,
        ]);

        $builder->add('position', EntityType::class, [
            'autocomplete' => true,
            'class' => Position::class,
            'required' => true,
            'placeholder' => 'Select a position',
        ]);

        $builder->add('location', EntityType::class, [
            'autocomplete' => true,
            'class' => Location::class,
            'required' => true,
            'placeholder' => 'Select a location',
        ]);

        $builder->add('startTime', TimeType::class, [
            'required' => false,
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            'help' => 'Optional start time for the shift',
        ]);

        $builder->add('endTime', TimeType::class, [
            'required' => false,
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            'help' => 'Optional end time for the shift',
        ]);

        $builder->add('requiredMin', NumberType::class, [
            'required' => false,
            'help' => 'Minimum number of people required for this shift',
        ]);

        $builder->add('requiredMax', NumberType::class, [
            'required' => false,
            'help' => 'Maximum number of people required for this shift',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShiftTemplate::class,
        ]);
    }
}
