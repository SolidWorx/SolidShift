<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Schedule;

use App\Attribute\Route;
use App\Entity\Schedule;
use App\Enum\ScheduleType;
use App\Repository\ScheduleRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use function iterator_to_array;

#[Route('/schedules', name: self::ROUTE_NAME, methods: ['GET'], siteAware: true)]
final class ScheduleList extends AbstractController
{
    public const ROUTE_NAME = 'schedule.list';

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository
    ) {
    }

    /**
     * @return array{schedules: array<Schedule>}
     */
    #[Template('schedule/list.html.twig')]
    public function __invoke(Request $request): array
    {
        $scheduleType = ScheduleType::from($request->query->getString('type', ScheduleType::SINGLE->value));

        return [
            'schedules' => iterator_to_array($this->scheduleRepository->findActiveSchedules($scheduleType))
        ];
    }
}
