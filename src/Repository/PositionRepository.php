<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Position>
 */
class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function save(Position $position, bool $flush = false): void
    {
        $this->getEntityManager()->persist($position);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Position $position, bool $flush = false): void
    {
        $this->getEntityManager()->remove($position);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
