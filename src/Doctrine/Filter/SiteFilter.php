<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Doctrine\Filter;

use App\Entity\UserSiteAccess;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Query\Filter\SQLFilter;

final class SiteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (! $this->hasParameter('site')) {
            return '';
        }

        if ($targetEntity->name === UserSiteAccess::class) {
            return '';
        }

        try {
            $mapping = $targetEntity->getAssociationMapping('site');

            if (! isset($mapping['targetToSourceKeyColumns']['id'])) {
                return '';
            }

            return sprintf('%s.%s = %s', $targetTableAlias, $mapping['targetToSourceKeyColumns']['id'], $this->getParameter('site'));
        } catch (MappingException) {
            return '';
        }
    }
}
