<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Security\Voter;

use App\Entity\Site;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function Symfony\Component\String\u;

/**
 * @extends Voter<string, UserRole>
 */
final class UserSiteAccessVoter extends Voter
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (! $this->getUserRole($attribute) instanceof UserRole) {
            return false;
        }

        return $subject instanceof Site || $this->getSite() instanceof Site;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $subject ??= $this->getSite();

        if (! $subject instanceof Site) {
            return false;
        }

        $userRole = $this->getUserRole($attribute);
        assert($userRole instanceof UserRole);

        foreach ($user->getSiteAccess() as $access) {
            if ($access->getSite() !== $subject) {
                continue;
            }

            if ($access->getRole() !== $userRole) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function getUserRole(string $attribute): ?UserRole
    {
        return UserRole::tryFrom(
            u($attribute)
                ->replace('ROLE_', '')
                ->lower()
                ->toString()
        );
    }

    private function getSite(): ?Site
    {
        $request = $this->requestStack->getCurrentRequest();

        if (! $request instanceof Request) {
            return null;
        }

        $site = $request->attributes->get('site');

        if (! $site instanceof Site) {
            return null;
        }

        return $site;
    }
}
