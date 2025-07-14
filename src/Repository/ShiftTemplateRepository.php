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

use App\Entity\ShiftTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShiftTemplate>
 */
class ShiftTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShiftTemplate::class);
    }

    public function save(ShiftTemplate $shiftTemplate, bool $flush = false): void
    {
        $this->getEntityManager()->persist($shiftTemplate);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShiftTemplate $shiftTemplate, bool $flush = false): void
    {
        $this->getEntityManager()->remove($shiftTemplate);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
