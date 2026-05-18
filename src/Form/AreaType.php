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
use App\Form\Transformer\AreaModelTransformer;
use App\Repository\AreaRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_key_exists;

/**
 * @extends AbstractType<Area>
 */
final class AreaType extends AbstractType
{
    public function __construct(
        private readonly AreaRepository $areaRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        if ($this->areaRepository->count([]) > 0) {
            $areas = $this->getAreas();

            if (array_key_exists('data', $options) && $options['data'] instanceof Area) {
                unset($areas[(string) $options['data']]);
            }

            $builder->add('parent', ChoiceType::class, [
                'required' => false,
                'help' => 'You can nest areas under other areas, E.G an Office in a floor in a building. Leave blank if this is a top level area.',
                'choices' => $areas,
            ]);

            $builder->get('parent')->addModelTransformer(new AreaModelTransformer($this->areaRepository));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getAreas(): array
    {
        $areas = [];

        foreach ($this->areaRepository->findBy(['parent' => null], ['name' => 'asc']) as $area) {
            $areas[(string) $area] = $area->getId()->toBase32();

            foreach ($area->getChildren() as $child) {
                $areas[(string) $child] = $child->getId()->toBase32();
            }
        }

        return $areas;
    }
}
