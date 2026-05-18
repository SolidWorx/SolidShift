<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\User\Invite;

use App\Attribute\Route;
use App\Controller\User\UserList;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function bin2hex;
use function random_bytes;

#[Route('/user/self-registration-link/{action}', name: SelfRegistrationLink::ROUTE_NAME, siteAware: true, methods: ['POST'])]
#[IsGranted('ROLE_ADMIN')]
final class SelfRegistrationLink extends AbstractController
{
    public const string ROUTE_NAME = 'user.self_registration_link';

    public function __construct(
        private readonly SiteRepository $siteRepository,
    ) {
    }

    public function __invoke(Site $site, string $action): Response
    {
        match ($action) {
            'rotate' => $site->setSelfRegistrationToken(bin2hex(random_bytes(16))),
            'disable' => $site->setSelfRegistrationToken(null),
            default => throw new BadRequestHttpException('Unknown action'),
        };

        $this->siteRepository->save($site);

        $this->addFlash('success', $action === 'rotate'
            ? 'Self-registration link generated.'
            : 'Self-registration link disabled.');

        return $this->redirectToRoute(UserList::ROUTE_NAME, ['site' => $site->getSlug()]);
    }
}
