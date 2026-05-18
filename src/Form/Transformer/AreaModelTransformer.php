<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form\Transformer;

use App\Entity\Area;
use App\Repository\AreaRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Area, string>
 */
final readonly class AreaModelTransformer implements DataTransformerInterface
{
    public function __construct(
        private AreaRepository $areaRepository
    ) {
    }

    public function transform(mixed $value): ?string
    {
        if (! $value instanceof Area) {
            return null;
        }

        return $value->getId()->toBase32();
    }

    public function reverseTransform(mixed $value): ?Area
    {
        if (null === $value) {
            return null;
        }

        return $this->areaRepository->find($value);
    }
}
