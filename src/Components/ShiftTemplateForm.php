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

use App\Entity\ShiftTemplate;
use App\Entity\Site;
use App\Form\ShiftTemplateType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ShiftTemplateForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    /**
     * On create this is a freshly-instantiated (unsaved) ShiftTemplate, which
     * can't be hydrated by the LiveComponent after a re-render. The `site`
     * LiveProp below is what survives across re-renders for both create and
     * edit flows.
     */
    #[LiveProp]
    public ?ShiftTemplate $initialFormData = null;

    #[LiveProp]
    public ?Site $site = null;

    /**
     * @return FormInterface<ShiftTemplate>
     */
    protected function instantiateForm(): FormInterface
    {
        if (! $this->site instanceof Site) {
            throw new LogicException('ShiftTemplateForm requires a site LiveProp.');
        }

        $shiftTemplate = $this->initialFormData ?? new ShiftTemplate(organisation: $this->site->getOrganisation());

        return $this->createForm(ShiftTemplateType::class, $shiftTemplate, [
            'site' => $this->site,
            'organisation' => $this->site->getOrganisation(),
        ]);
    }
}
