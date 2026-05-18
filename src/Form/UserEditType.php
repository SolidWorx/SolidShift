<?php

declare(strict_types=1);

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
use App\Entity\User;
use App\Entity\UserSiteAccess;
use App\Enum\MembershipRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use function assert;

/**
 * @extends AbstractType<User>
 */
final class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $site = $options['site'];
        assert($site instanceof Site);

        [$orderedAreas, $depths] = self::orderAreasByTree($site);

        $organisation = $site->getOrganisation();
        $roles = $organisation !== null ? [...$organisation->getRoles()] : [];

        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('firstName', null, ['required' => true])
            ->add('lastName', null, ['required' => false])
            ->add('phone', PhoneType::class, [
                'label' => 'Mobile Number',
                'required' => false,
            ])
            ->add('membershipRole', EnumType::class, [
                'class' => MembershipRole::class,
                'required' => true,
                'label' => 'Membership',
                'getter' => static function (User $user) use ($site): MembershipRole {
                    foreach ($user->getSiteAccess() as $access) {
                        if ($access->getSite() === $site) {
                            return $access->getRole();
                        }
                    }

                    return MembershipRole::ROLE_USER;
                },
                'setter' => static function (User $user, MembershipRole $value) use ($site): void {
                    foreach ($user->getSiteAccess() as $access) {
                        if ($access->getSite() === $site) {
                            $access->setRole($value);

                            return;
                        }
                    }

                    $user->getSiteAccess()->add(new UserSiteAccess($user, $site, $value));
                },
            ])
            ->add('jobRoles', EntityType::class, [
                'class' => Role::class,
                'choices' => $roles,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choice_label' => 'name',
                'label' => 'Job roles',
                'help' => 'Which job roles this user can be scheduled as on this site.',
                'getter' => static fn (User $user): array => $user->getRolesForSite($site),
                'setter' => static function (User $user, iterable $roles) use ($site): void {
                    $user->setRolesForSite($site, $roles);
                },
            ])
            ->addDependent('managedAreas', 'membershipRole', static function (DependentField $field, ?MembershipRole $membership) use ($orderedAreas, $depths, $site): void {
                if ($membership !== MembershipRole::ROLE_ADMIN && $membership !== MembershipRole::ROLE_MANAGER) {
                    return;
                }

                $field->add(EntityType::class, [
                    'class' => Area::class,
                    'choices' => $orderedAreas,
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'choice_label' => 'name',
                    'choice_attr' => static fn (Area $area): array => [
                        'data-depth' => (string) ($depths[$area->getId()->toBase32()] ?? 0),
                    ],
                    'label' => 'Managed areas',
                    'help' => 'Areas where this user has manager privileges.',
                    'getter' => static fn (User $user): array => $user->getManagedAreas($site),
                    'setter' => static function (User $user, iterable $areas) use ($site): void {
                        $user->setManagedAreasForSite($site, $areas);
                    },
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
        $resolver->setRequired(['site']);
        $resolver->setAllowedTypes('site', Site::class);
    }

    /**
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
