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
use function is_array;
use function ltrim;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Route
{
    private ?string $path = null;

    /**
     * @var list<string>
     */
    private array $localizedPaths = [];

    /**
     * @var list<string>
     */
    private array $methods;

    /**
     * @var list<string>
     */
    private array $schemes;

    /**
     * @param list<(string | Stringable)> $requirements
     * @param list<string>|string           $path
     * @param list<string>|string           $methods
     * @param list<string>|string           $schemes
     * @param array<string, mixed>           $defaults
     * @param array<string, mixed>           $options
     */
    public function __construct(
        string | array  $path = null,
        private ?string $name = null,
        private array   $requirements = [],
        private array   $options = [],
        private array   $defaults = [],
        private ?string $host = null,
        array | string  $methods = [],
        array | string  $schemes = [],
        private ?string $condition = null,
        private ?int    $priority = null,
        string          $locale = null,
        string          $format = null,
        bool            $utf8 = null,
        bool            $stateless = null,
        private ?string $env = null,
        readonly bool   $siteAware = false,
    ) {
        if (is_array($path)) {
            $this->localizedPaths = $path;
        } else {
            $path = ($siteAware ? '/s/{site}/' : '/') . ltrim((string) $path, '/');
            $this->path = $path;
        }

        $this->setMethods($methods);
        $this->setSchemes($schemes);

        if (null !== $locale) {
            $this->defaults['_locale'] = $locale;
        }

        if (null !== $format) {
            $this->defaults['_format'] = $format;
        }

        if (null !== $utf8) {
            $this->options['utf8'] = $utf8;
        }

        if (null !== $stateless) {
            $this->defaults['_stateless'] = $stateless;
        }
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param list<string> $localizedPaths
     */
    public function setLocalizedPaths(array $localizedPaths): void
    {
        $this->localizedPaths = $localizedPaths;
    }

    /**
     * @return list<string>
     */
    public function getLocalizedPaths(): array
    {
        return $this->localizedPaths;
    }

    public function setHost(string $pattern): void
    {
        $this->host = $pattern;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return list<(string | Stringable)>
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * @param list<(string | Stringable)> $requirements
     */
    public function setRequirements(array $requirements): void
    {
        $this->requirements = $requirements;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function setDefaults(array $defaults): void
    {
        $this->defaults = $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @param list<string>|string $schemes
     */
    public function setSchemes(array | string $schemes): void
    {
        $this->schemes = (array) $schemes;
    }

    /**
     * @return list<string>
     */
    public function getSchemes(): array
    {
        return $this->schemes;
    }

    /**
     * @param list<string>|string $methods
     */
    public function setMethods(array | string $methods): void
    {
        $this->methods = (array) $methods;
    }

    /**
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setCondition(?string $condition): void
    {
        $this->condition = $condition;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setEnv(?string $env): void
    {
        $this->env = $env;
    }

    public function getEnv(): ?string
    {
        return $this->env;
    }
}
