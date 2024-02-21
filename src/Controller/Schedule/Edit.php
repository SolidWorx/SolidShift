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
use App\Form\ScheduleType;
use App\Repository\ScheduleRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/schedule/edit/{schedule}', name: Edit::ROUTE_NAME, siteAware: true)]
final class Edit extends AbstractController
{
    public const ROUTE_NAME = 'schedule.edit';

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template('schedule/create.html.twig')]
    public function __invoke(Request $request, Site $site, Schedule $schedule): array|Response
    {
        $form = $this->createForm(ScheduleType::class, $schedule, ['edit' => true])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->scheduleRepository->save($schedule);

            $this->addFlash('success', 'Schedule updated successfully');

            return $this->redirectToRoute(ScheduleList::ROUTE_NAME);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
