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
use App\Entity\Site;
use App\Enum\ScheduleType;
use App\Filter\ScheduleFilter;
use App\Model\ScheduleDate;
use App\Schedule\ScheduleList;
use AppendIterator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Iterator;
use IteratorIterator;
use Psr\Clock\ClockInterface;
use RuntimeException;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Traversable;
use function assert;
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
    public function __construct(
        ManagerRegistry $registry,
        private readonly RequestStack $requestStack,
        private readonly ClockInterface $clock
    ) {
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
    public function findActiveRecurringSchedules(?Site $site = null): Traversable
    {
        $qb = $this->createQueryBuilder('s');

        $today = CarbonImmutable::instance($this->clock->now())->startOfDay();

        $qb->where('s.scheduleType = :type')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.endDate'),
                    $qb->expr()->gte('s.endDate', ':today')
                )
            )
            ->andWhere('s.site = :site')
            ->setParameter('site', ($site ?? $this->getSite())->getId(), UlidType::NAME)
            ->setParameter('type', ScheduleType::RECURRING)
            ->setParameter('today', $today)
            ->orderBy('s.startDate', 'ASC');

        foreach ($qb->getQuery()->toIterable() as $schedule) {
            /** @var Schedule $schedule */
            if (ScheduleFilter::isScheduleCompleted($schedule, $today)) {
                continue;
            }

            yield $schedule;
        }
    }

    /**
     * @return Traversable<Schedule>
     */
    public function findActiveSingleSchedules(?Site $site = null): Traversable
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
            ->andWhere('s.site = :site')
            ->setParameter('site', $site ?? $this->getSite())
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
    public function getScheduleListForActiveSchedules(?Site $site = null): ScheduleList
    {
        /** @var AppendIterator<int, Schedule, Iterator<Schedule>> $append */
        $append = new AppendIterator();
        $append->append(new IteratorIterator($this->findActiveSingleSchedules($site)));
        $append->append(new IteratorIterator($this->findActiveRecurringSchedules($site)));

        return new ScheduleList($append, $this->clock);
    }

    /**
     * @return ScheduleList<ScheduleDate>
     * @throws DateInvalidTimeZoneException|DateMalformedStringException
     */
    public function getScheduleListForPeriod(CarbonInterface $start, CarbonInterface $end, ?Site $site = null): ScheduleList
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.startDate', ':start'),
                    $qb->expr()->lte('s.startDate', ':end')
                ),
                $qb->expr()->lte('s.startDate', ':start'),
            )
        )
            ->andWhere('s.site = :site')
            ->setParameter('site', ($site ?? $this->getSite())->getId(), UlidType::NAME)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.startDate', 'ASC');

        /** @var iterable<Schedule> $schedules */
        $schedules = $qb->getQuery()->toIterable();

        return new ScheduleList($schedules, new MockClock($start));
    }

    private function getSite(): Site
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            throw new RuntimeException('No request found');
        }

        $site = $request->attributes->get('site');
        if (!$site instanceof Site) {
            throw new RuntimeException('No site found');
        }
        return $site;
    }
}
