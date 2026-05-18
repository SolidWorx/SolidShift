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

use App\Entity\User;
use Closure;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use function array_map;
use function is_string;

/**
 * @extends AbstractType<User>
 */
#[AsEntityAutocompleteField]
class UserAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => User::class,
            'searchable_fields' => ['firstName', 'lastName'],
            'query_builder' => fn (Options $options): Closure => static function (EntityRepository $em) use ($options): QueryBuilder {
                $qb = $em->createQueryBuilder('u');

                $excludeUsers = $options['extra_options']['exclude_users'] ?? [];
                if ([] !== $excludeUsers) {
                    $qb->andWhere($qb->expr()->notIn('u.id', ':users'))
                        ->setParameter(
                            'users',
                            array_map(
                                static fn (string $id): string => Ulid::fromBase32($id)->toBinary(),
                                $excludeUsers,
                            )
                        );
                }

                $siteId = $options['extra_options']['eligible_site_id'] ?? null;
                $roleIds = $options['extra_options']['eligible_role_ids'] ?? [];

                if (is_string($siteId) && $siteId !== '' && $roleIds !== []) {
                    $qb->innerJoin('u.roleAssignments', 'ra')
                        ->andWhere('ra.site = :eligibleSite')
                        ->andWhere('ra.role IN (:eligibleRoles)')
                        ->setParameter('eligibleSite', Ulid::fromBase32($siteId)->toBinary())
                        ->setParameter(
                            'eligibleRoles',
                            array_map(
                                static fn (string $id): string => Ulid::fromBase32($id)->toBinary(),
                                $roleIds,
                            )
                        );
                } elseif (is_string($siteId) && $siteId !== '') {
                    $qb->innerJoin('u.siteAccess', 'sa')
                        ->andWhere('sa.site = :eligibleSite')
                        ->setParameter('eligibleSite', Ulid::fromBase32($siteId)->toBinary());
                }

                return $qb;
            },
            'label' => 'Search User',
            'choice_label' => 'fullName',
            'multiple' => true,
            'constraints' => [
                new Assert\Count(min: 1, minMessage: 'Please select at least one user'),
            ],
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
