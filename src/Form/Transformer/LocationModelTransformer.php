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

use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Location, string>
 */
final readonly class LocationModelTransformer implements DataTransformerInterface
{
    public function __construct(
        private LocationRepository $locationRepository
    ) {
    }

    public function transform(mixed $value): ?string
    {
        if (! $value instanceof Location) {
            return null;
        }

        return $value->getId()->toBase32();
    }

    public function reverseTransform(mixed $value): ?Location
    {
        if (null === $value) {
            return null;
        }

        return $this->locationRepository->find($value);
    }
}
