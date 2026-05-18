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

namespace App\Controller\User;

use App\Attribute\Route;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserSiteAccess;
use App\Enum\MembershipRole;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users/{user}/edit', name: Edit::ROUTE_NAME, siteAware: true)]
#[IsGranted('ROLE_ADMIN')]
final class Edit extends AbstractController
{
    public const string ROUTE_NAME = 'user.edit';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return array{user: User, site: Site}|Response
     */
    #[Template('user/edit.html.twig')]
    public function __invoke(Request $request, Site $site, User $user): array|Response
    {
        if (! $this->userHasAccess($user, $site)) {
            throw $this->createNotFoundException('User has no access to this site.');
        }

        $form = $this->createForm(UserEditType::class, $user, ['site' => $site])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMembership = $form->get('membershipRole')->getData();

            if ($newMembership === MembershipRole::ROLE_USER) {
                $user->setManagedAreasForSite($site, []);
            }

            $this->userRepository->save($user);

            $this->addFlash('success', sprintf('%s has been updated.', $user->getFullName()));

            return $this->redirectToRoute(UserList::ROUTE_NAME, ['site' => $site->getSlug()]);
        }

        return [
            'user' => $user,
            'site' => $site,
        ];
    }

    private function userHasAccess(User $user, Site $site): bool
    {
        foreach ($user->getSiteAccess() as $access) {
            if ($access->getSite() === $site && $access instanceof UserSiteAccess) {
                return true;
            }
        }

        return false;
    }
}
