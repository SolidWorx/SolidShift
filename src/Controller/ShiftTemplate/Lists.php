<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\ShiftTemplate;

use App\Attribute\Route;
use App\Entity\ShiftTemplate;
use App\Entity\Site;
use App\Enum\MembershipRole;
use App\Form\ShiftTemplateType;
use App\Repository\ShiftTemplateRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/shift-templates', name: Lists::ROUTE_NAME, siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Lists extends AbstractController
{
    public const string ROUTE_NAME = 'shift_template.list';

    public function __construct(
        private readonly ShiftTemplateRepository $shiftTemplateRepository
    ) {
    }

    /**
     * @return array{form: FormView, shiftTemplates: array<int, ShiftTemplate>}
     */
    #[Template(template: 'shift_template/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'form' => $this->createForm(ShiftTemplateType::class, null, [
                'site' => $site,
                'organisation' => $site->getOrganisation(),
            ])->createView(),
            'shiftTemplates' => $this->shiftTemplateRepository->findForSite($site),
        ];
    }
}
