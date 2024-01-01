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

use App\Entity\UserInvite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserInvite>
 *
 * @method UserInvite|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInvite|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInvite[]    findAll()
 * @method UserInvite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserInviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInvite::class);
    }

    public function save(UserInvite $userInvite): void
    {
        $em = $this->getEntityManager();

        $em->persist($userInvite);
        $em->flush();
    }

    public function delete(UserInvite $invite): void
    {
        $em = $this->getEntityManager();

        $em->remove($invite);
        $em->flush();
    }
}
