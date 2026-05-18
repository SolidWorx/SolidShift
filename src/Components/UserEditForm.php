<?php

declare(strict_types=1);

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Components;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserEditType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class UserEditForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?User $user = null;

    #[LiveProp]
    public ?Site $site = null;

    /**
     * @return FormInterface<User>
     */
    protected function instantiateForm(): FormInterface
    {
        if (! $this->user instanceof User || ! $this->site instanceof Site) {
            throw new LogicException('UserEditForm requires user and site LiveProps.');
        }

        return $this->createForm(UserEditType::class, $this->user, [
            'site' => $this->site,
        ]);
    }
}
