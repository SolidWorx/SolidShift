<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Position;
use App\Entity\ScheduleShift;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleShiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('location', EntityType::class, [
                'autocomplete' => true,
                'class' => Location::class,
            ])
            ->add('position', EntityType::class, [
                'autocomplete' => true,
                'class' => Position::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScheduleShift::class,
        ]);
    }
}
