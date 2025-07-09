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

use App\Entity\ShiftAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShiftAssignment>
 *
 * @method ShiftAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShiftAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShiftAssignment[]    findAll()
 * @method ShiftAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShiftAssignment::class);
    }
}
