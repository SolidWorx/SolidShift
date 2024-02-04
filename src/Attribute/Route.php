<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Attribute;

use Attribute;
use Stringable;
use Symfony\Component\Routing\Attribute\Route as BaseRoute;
use function is_array;
use function ltrim;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route extends BaseRoute
{
    /**
     * @param list<(string | Stringable)> $requirements
     * @param list<string>|string           $path
     * @param list<string>|string           $methods
     * @param list<string>|string           $schemes
     * @param array<string, mixed>           $defaults
     * @param array<string, mixed>           $options
     */
    public function __construct(
        string | array $path = null,
        ?string        $name = null,
        array          $requirements = [],
        array          $options = [],
        array          $defaults = [],
        ?string        $host = null,
        array | string $methods = [],
        array | string $schemes = [],
        ?string        $condition = null,
        ?int           $priority = null,
        string         $locale = null,
        string         $format = null,
        bool           $utf8 = null,
        bool           $stateless = null,
        ?string        $env = null,
        readonly bool  $siteAware = false,
    ) {
        if (! is_array($path)) {
            $path = ($siteAware ? '/s/{site}/' : '/') . ltrim((string) $path, '/');
        }

        parent::__construct(
            path: $path,
            name: $name,
            requirements: $requirements,
            options: $options,
            defaults: $defaults,
            host: $host,
            methods: $methods,
            schemes: $schemes,
            condition: $condition,
            priority: $priority,
            locale: $locale,
            format: $format,
            utf8: $utf8,
            stateless: $stateless,
            env: $env,
        );
    }
}
