<?php

namespace App\Components;

use App\Entity\Schedule;
use App\Form\ScheduleType;
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

    #[LiveProp]
    public ?Schedule $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ScheduleType::class, $this->initialFormData);
    }
}
