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
use App\Entity\Site;
use App\Enum\ScheduleType as ScheduleTypeEnum;
use App\Form\ScheduleType;
use App\Repository\LocationRepository;
use App\Repository\PositionRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ShiftTemplateRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/schedule/create', name: Create::ROUTE_NAME, siteAware: true)]
final class Create extends AbstractController
{
    public const string ROUTE_NAME = 'schedule.create';

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly ShiftTemplateRepository $shiftTemplateRepository,
        private readonly PositionRepository $positionRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    /**
     * @return array{form: FormView, shiftTemplates: array, positions: array, locations: array}|Response
     */
    #[Template('schedule/create.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $schedule = new Schedule(
            scheduleType: ScheduleTypeEnum::tryFrom($request->query->getString('type')),
            site: $site,
        );

        $form = $this->createForm(ScheduleType::class, $schedule, ['edit' => false])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->scheduleRepository->save($schedule);

            $this->addFlash('success', 'Schedule created successfully');

            return $this->redirectToRoute(ScheduleList::ROUTE_NAME);
        }

        return [
            'form' => $form->createView(),
            'shiftTemplates' => $this->shiftTemplateRepository->findAll(),
            'positions' => $this->positionRepository->findAll(),
            'locations' => $this->locationRepository->findAll(),
        ];
    }
}
