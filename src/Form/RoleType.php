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
use App\Entity\Role;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function assert;

/**
 * @extends AbstractType<Role>
 */
final class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $site = $options['site'];
        assert($site instanceof Site);

        [$orderedAreas, $depths] = self::orderAreasByTree($site);

        $builder
            ->add('name')
            ->add('allowedAreas', EntityType::class, [
                'class' => Area::class,
                'choices' => $orderedAreas,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choice_label' => 'name',
                'choice_attr' => static fn (Area $area): array => [
                    'data-depth' => (string) ($depths[$area->getId()->toBase32()] ?? 0),
                ],
                'label' => 'Allowed areas',
                'help' => 'Leave empty to allow this role in any area. Each area is independent — selecting "Bar" does not auto-select children like "Smoking section".',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Role::class]);
        $resolver->setRequired(['site']);
        $resolver->setAllowedTypes('site', Site::class);
    }

    /**
     * Depth-first walk of the site's area tree. Returns the ordered list and a
     * depth map keyed by Area ID (base32) so the form theme can indent each
     * checkbox row.
     *
     * @return array{0: list<Area>, 1: array<string, int>}
     */
    private static function orderAreasByTree(Site $site): array
    {
        $ordered = [];
        $depths = [];

        $walk = static function (Area $area, int $depth) use (&$walk, &$ordered, &$depths): void {
            $ordered[] = $area;
            $depths[$area->getId()->toBase32()] = $depth;

            foreach ($area->getChildren(includeGrandChildren: false) as $child) {
                $walk($child, $depth + 1);
            }
        };

        foreach ($site->getAreas() as $area) {
            if ($area->getParent() === null) {
                $walk($area, 0);
            }
        }

        return [$ordered, $depths];
    }
}
