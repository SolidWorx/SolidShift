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

use App\Entity\UserSiteAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSiteAccess>
 *
 * @method UserSiteAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSiteAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSiteAccess[]    findAll()
 * @method UserSiteAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSiteAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSiteAccess::class);
    }
}
