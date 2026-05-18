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

use App\Entity\Occurrence;
use App\Entity\Shift;
use App\Entity\ShiftRequirement;
use App\Entity\Site;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function array_map;

/**
 * @extends ServiceEntityRepository<Shift>
 */
final class ShiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shift::class);
    }

    public function save(Shift $shift): void
    {
        $em = $this->getEntityManager();
        $em->persist($shift);
        $em->flush();
    }

    public function delete(Shift $shift): void
    {
        $em = $this->getEntityManager();
        $em->remove($shift);
        $em->flush();
    }

    /**
     * @return list<Shift>
     */
    public function findByOccurrence(Occurrence $occurrence): array
    {
        /** @var list<Shift> $shifts */
        $shifts = $this->findBy(['occurrence' => $occurrence]);

        return $shifts;
    }

    /**
     * @return list<Shift>
     */
    public function findByOccurrenceAndRequirement(Occurrence $occurrence, ShiftRequirement $requirement): array
    {
        /** @var list<Shift> $shifts */
        $shifts = $this->findBy(['occurrence' => $occurrence, 'requirement' => $requirement]);

        return $shifts;
    }

    public function countByOccurrenceAndRequirement(Occurrence $occurrence, ShiftRequirement $requirement): int
    {
        return $this->count(['occurrence' => $occurrence, 'requirement' => $requirement]);
    }

    /**
     * Shifts a user already has on the given date, scoped to the site via
     * Occurrence -> OccurrenceTemplate -> Schedule -> Site. Used by the
     * eligibility service to detect overlapping assignments.
     *
     * @return list<Shift>
     */
    public function findByUserAndDate(User $user, DateTimeImmutable $date, Site $site): array
    {
        /** @var list<Shift> $shifts */
        $shifts = $this->createQueryBuilder('s')
            ->innerJoin('s.occurrence', 'o')
            ->innerJoin('o.template', 't')
            ->innerJoin('t.schedule', 'sc')
            ->andWhere('s.user = :user')
            ->andWhere('o.date = :date')
            ->andWhere('sc.site = :site')
            ->setParameter('user', $user)
            ->setParameter('date', $date->setTime(0, 0))
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult();

        return $shifts;
    }

    /**
     * Bulk-load all shifts attached to a set of occurrences in a single query.
     * Returned map is keyed by "{occurrenceId}:{requirementId}" so the roster
     * grid can look up a cell's assignments in O(1).
     *
     * @param list<Occurrence> $occurrences
     *
     * @return array<string, list<Shift>>
     */
    public function findForOccurrences(array $occurrences): array
    {
        if ($occurrences === []) {
            return [];
        }

        /** @var list<Shift> $shifts */
        $shifts = $this->createQueryBuilder('s')
            ->andWhere('s.occurrence IN (:occurrences)')
            ->setParameter('occurrences', array_map(static fn (Occurrence $o) => $o->getId()->toBinary(), $occurrences))
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($shifts as $shift) {
            $key = $shift->getOccurrence()->getId()->toBase32() . ':' . $shift->getRequirement()->getId()->toBase32();
            $map[$key][] = $shift;
        }

        return $map;
    }
}
