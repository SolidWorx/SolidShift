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

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withImportNames(importNames: true, importDocBlockNames: true, importShortClasses: true, removeUnusedImports: true)
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withSkip([
        RemoveAlwaysTrueIfConditionRector::class => [
            __DIR__ . '/src/Security/Voter/UserSiteAccessVoter.php',
        ],
        AnnotationToAttributeRector::class => [
            __DIR__ . '/src/Entity/Site.php',
            __DIR__ . '/src/Entity/User.php',
            __DIR__ . '/src/Entity/UserSiteAccess.php',
        ],
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/Entity/*.php',
        ],
    ])
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withAttributesSets(symfony: true)
    ->withPhpSets()
    ->withPreparedSets(symfonyConfigs: true)
    ->withSets([
        // General
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::PHP_84,
        SetList::STRICT_BOOLEANS,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        SetList::CARBON,

        // PHP
        LevelSetList::UP_TO_PHP_84,

        // PHPUnit
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_120,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,

        // Doctrine
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::TYPED_COLLECTIONS,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,
    ])
;
