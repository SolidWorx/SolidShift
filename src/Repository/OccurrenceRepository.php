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
use App\Entity\OccurrenceTemplate;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function array_map;

/**
 * @extends ServiceEntityRepository<Occurrence>
 */
final class OccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Occurrence::class);
    }

    /**
     * Materialise the Occurrence for a given (template, date) pair, creating
     * and persisting one if it does not yet exist. The (template_id, date)
     * unique constraint on the entity guarantees deduplication.
     */
    public function findOrCreate(OccurrenceTemplate $template, DateTimeImmutable $date): Occurrence
    {
        $existing = $this->findOneBy(['template' => $template, 'date' => $date]);

        if ($existing instanceof Occurrence) {
            return $existing;
        }

        $occurrence = new Occurrence($template, $date);

        $em = $this->getEntityManager();
        $em->persist($occurrence);
        $em->flush();

        return $occurrence;
    }

    /**
     * Batched companion to findOrCreate(): looks up all matching Occurrences
     * in one query, creates the missing ones, flushes once. The roster grid
     * uses this to avoid N+1 when rendering many (template, date) cells.
     *
     * @param list<array{template: OccurrenceTemplate, date: DateTimeImmutable}> $pairs
     *
     * @return array<string, Occurrence>  keyed by "{templateId}:{Y-m-d}"
     */
    public function findOrCreateForTemplates(array $pairs): array
    {
        if ($pairs === []) {
            return [];
        }

        $templates = [];
        foreach ($pairs as $pair) {
            $templates[$pair['template']->getId()->toBase32()] = $pair['template'];
        }

        /** @var list<Occurrence> $existing */
        $existing = $this->createQueryBuilder('o')
            ->andWhere('o.template IN (:templates)')
            ->setParameter('templates', array_map(static fn (OccurrenceTemplate $t) => $t->getId()->toBinary(), array_values($templates)))
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($existing as $occurrence) {
            $key = $occurrence->getTemplate()->getId()->toBase32() . ':' . $occurrence->getDate()->format('Y-m-d');
            $map[$key] = $occurrence;
        }

        $em = $this->getEntityManager();
        $created = false;

        foreach ($pairs as $pair) {
            $date = $pair['date']->setTime(0, 0);
            $key = $pair['template']->getId()->toBase32() . ':' . $date->format('Y-m-d');

            if (isset($map[$key])) {
                continue;
            }

            $occurrence = new Occurrence($pair['template'], $date);
            $em->persist($occurrence);
            $map[$key] = $occurrence;
            $created = true;
        }

        if ($created) {
            $em->flush();
        }

        return $map;
    }
}
