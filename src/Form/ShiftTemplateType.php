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

use App\Entity\Area;
use App\Entity\Organisation;
use App\Entity\Role;
use App\Entity\ShiftTemplate;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use function assert;

/**
 * @extends AbstractType<ShiftTemplate>
 */
final class ShiftTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $site = $options['site'];
        $organisation = $options['organisation'];
        assert($site instanceof Site);
        assert($organisation instanceof Organisation);

        $siteAreas = $site->getAreas();
        $builder = new DynamicFormBuilder($builder);

        $builder->add('name', TextType::class, [
            'required' => true,
            'empty_data' => '',
        ]);

        $builder->add('description', TextareaType::class, [
            'required' => false,
        ]);

        $builder->add('role', EntityType::class, [
            'class' => Role::class,
            'choices' => $organisation->getRoles(),
            'choice_label' => 'name',
            'autocomplete' => true,
            'required' => true,
            'placeholder' => 'Select a role',
        ]);

        $builder->addDependent('area', 'role', static function (DependentField $field, ?Role $role) use ($siteAreas): void {
            $allowedAreas = $role?->getAllowedAreas();
            $choices = $allowedAreas !== null && ! $allowedAreas->isEmpty()
                ? $allowedAreas
                : $siteAreas;

            $field->add(EntityType::class, [
                'class' => Area::class,
                'choices' => $choices,
                'choice_label' => 'name',
                'autocomplete' => true,
                'required' => false,
                'placeholder' => 'Any area',
                'help' => $role !== null && $allowedAreas !== null && ! $allowedAreas->isEmpty()
                    ? sprintf('Limited to the areas %s is allowed in.', $role->getName())
                    : null,
            ]);
        });

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
        $resolver->setDefaults(['data_class' => ShiftTemplate::class]);
        $resolver->setRequired(['site', 'organisation']);
        $resolver->setAllowedTypes('site', Site::class);
        $resolver->setAllowedTypes('organisation', Organisation::class);
    }
}
