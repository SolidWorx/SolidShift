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

use App\Entity\Schedule;
use App\Enum\ScheduleType;
use App\Model\ScheduleDate;
use App\Schedule\ScheduleList;
use AppendIterator;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use IteratorIterator;
use Traversable;
use function date_default_timezone_get;

/**
 * @extends ServiceEntityRepository<Schedule>
 *
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function save(Schedule $schedule): void
    {
        $em = $this->getEntityManager();

        $em->persist($schedule);
        $em->flush();
    }

    /**
     * @return iterable<Schedule>
     */
    public function findActiveSchedules(ScheduleType $type): iterable
    {
        return match ($type) {
            ScheduleType::RECURRING => $this->findActiveRecurringSchedules(),
            ScheduleType::SINGLE => $this->findActiveSingleSchedules(),
        };
    }

    /**
     * @return Traversable<Schedule>
     */
    public function findActiveRecurringSchedules(): Traversable
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.scheduleType = :type')
            ->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('s.endDate'),
                $qb->expr()->gte('s.endDate', ':today')
            )
        )
            ->setParameter('type', ScheduleType::RECURRING)
            ->setParameter('today', CarbonImmutable::now(date_default_timezone_get())->startOfDay())
            ->orderBy('s.startDate', 'ASC');

        /** @var Traversable<Schedule> $return */
        $return = $qb->getQuery()->toIterable();

        return $return;
    }

    /**
     * @return Traversable<Schedule>
     */
    public function findActiveSingleSchedules(): Traversable
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.scheduleType = :type')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gte('s.startDate', ':today'),
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('s.endDate'),
                        $qb->expr()->lte('s.endDate', ':today'),
                    )
                )
            )
            ->setParameter('type', ScheduleType::SINGLE)
            ->setParameter('today', CarbonImmutable::now(date_default_timezone_get())->startOfDay())
            ->orderBy('s.startDate', 'ASC');

        /** @var Traversable<Schedule> $return */
        $return = $qb->getQuery()->toIterable();

        return $return;
    }

    /**
     * @return ScheduleList<ScheduleDate>
     */
    public function getScheduleListForActiveSchedules(): ScheduleList
    {
        /** @var AppendIterator<int, Schedule, Traversable<Schedule>> $append */
        $append = new AppendIterator();
        $append->append(new IteratorIterator($this->findActiveSingleSchedules()));
        $append->append(new IteratorIterator($this->findActiveRecurringSchedules()));

        return new ScheduleList($append);
    }
}
