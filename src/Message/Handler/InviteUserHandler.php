<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Message\Handler;

use App\Entity\UserInvite;
use App\Message\SendInvite;
use App\Repository\UserInviteRepository;
use App\Repository\UserRepository;
use App\Security\Signature\SignatureHasher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(handles: UserInvite::class)]
final readonly class InviteUserHandler
{
    public function __construct(
        private UserRepository       $userRepository,
        private SignatureHasher      $hasher,
        private UserInviteRepository $userInviteRepository,
        private MessageBusInterface  $messageBus,
    ) {
    }

    public function __invoke(UserInvite $userInvite): void
    {
        $user = null;

        if (null !== $userInvite->getEmail() && '' !== $userInvite->getEmail()) {
            $user = $this->userRepository->findOneBy(['username' => $userInvite->getEmail()]);
        } elseif (null !== $userInvite->getPhone() && '' !== $userInvite->getPhone()) {
            $user = $this->userRepository->findOneBy(['phone' => $userInvite->getPhone()]);
        }

        $userInvite->setUser($user);

        $userInvite->setHash(
            $this->hasher->generate(
                [
                    'email' => $userInvite->getEmail(),
                    'phone' => $userInvite->getPhone(),
                    'role' => $userInvite->getRole()->value,
                    'site' => $userInvite->getSite()->getId(),
                    'user' => $user?->getId(),
                ],
            )
        );

        $this->userInviteRepository->save($userInvite);

        $this->messageBus->dispatch(new SendInvite($userInvite));
    }
}
