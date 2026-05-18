<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Components;

use App\Entity\Schedule;
use App\Entity\Site;
use App\Form\ScheduleType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent]
final class ScheduleForm extends AbstractController
{
    use LiveCollectionTrait;
    use DefaultActionTrait;

    /**
     * On create this is a freshly-instantiated (unsaved) Schedule, which can't
     * be hydrated by the LiveComponent after a re-render. On edit the entity
     * is persisted and rehydrates via its Ulid. The `site` LiveProp below is
     * what actually survives across re-renders for both flows.
     */
    #[LiveProp]
    public ?Schedule $initialFormData = null;

    #[LiveProp]
    public ?Site $site = null;

    #[LiveProp]
    public bool $edit = false;

    /**
     * @return FormInterface<Schedule>
     */
    protected function instantiateForm(): FormInterface
    {
        $site = $this->initialFormData?->getSite() ?? $this->site;

        if (! $site instanceof Site) {
            throw new LogicException('ScheduleForm requires either an initial Schedule with a site or a site LiveProp.');
        }

        $schedule = $this->initialFormData ?? new Schedule(site: $site);

        return $this->createForm(ScheduleType::class, $schedule, [
            'edit' => $this->edit,
            'site' => $site,
            'organisation' => $site->getOrganisation(),
        ]);
    }
}
