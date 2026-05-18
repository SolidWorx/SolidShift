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

use App\Entity\Organisation;
use App\Entity\ShiftTemplate;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

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

    /**
     * Returns shift templates that are safe to render on the given site —
     * scoped to the site's organisation, and excluding templates whose `area`
     * lives in a different site (since the SiteFilter would block the lazy
     * fetch on render).
     *
     * @return list<ShiftTemplate>
     */
    public function findForSite(Site $site): array
    {
        $em = $this->getEntityManager();
        $filters = $em->getFilters();
        $siteFilterWasEnabled = $filters->isEnabled('site');

        if ($siteFilterWasEnabled) {
            $filters->disable('site');
        }

        $organisation = $site->getOrganisation();

        if (! $organisation instanceof Organisation) {
            return [];
        }

        try {
            /** @var list<ShiftTemplate> $results */
            $results = $this->createQueryBuilder('t')
                ->leftJoin('t.area', 'a')
                ->andWhere('t.organisation = :organisation')
                ->andWhere('t.area IS NULL OR a.site = :site')
                ->setParameter('organisation', $organisation->getId(), UlidType::NAME)
                ->setParameter('site', $site->getId(), UlidType::NAME)
                ->orderBy('t.name', 'ASC')
                ->getQuery()
                ->getResult();
        } finally {
            if ($siteFilterWasEnabled) {
                $filters->enable('site')->setParameter('site', $site->getId()->toBinary());
            }
        }

        return $results;
    }
}
