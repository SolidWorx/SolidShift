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
use App\Form\Transformer\LocationModelTransformer;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_key_exists;

/**
 * @extends AbstractType<Location>
 */
final class LocationType extends AbstractType
{
    public function __construct(
        private readonly LocationRepository $locationRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        if ($this->locationRepository->count([]) > 0) {
            $locations = $this->getLocations();

            if (array_key_exists('data', $options) && $options['data'] instanceof Location) {
                unset($locations[(string) $options['data']]);
            }

            $builder->add('parent', ChoiceType::class, [
                'required' => false,
                'help' => 'You can nest locations under other locations, E.G an Office in a floor in a building. Leave blank if this is a top level location.',
                'choices' => $locations,
            ]);

            $builder->get('parent')->addModelTransformer(new LocationModelTransformer($this->locationRepository));
        }

        $builder->add('positions', EntityType::class, [
            'class' => Position::class,
            'multiple' => true,
            'expanded' => false,
            'required' => false,
            'by_reference' => false,
            'help' => 'Select positions that are available at this location. Leave blank if no specific positions are tied to this location.',
            'autocomplete' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getLocations(): array
    {
        $locations = [];

        foreach ($this->locationRepository->findBy(['parent' => null], ['name' => 'asc']) as $location) {
            $locations[(string) $location] = $location->getId()->toBase32();

            foreach ($location->getChildren() as $child) {
                $locations[(string) $child] = $child->getId()->toBase32();
            }
        }

        return $locations;
    }
}
