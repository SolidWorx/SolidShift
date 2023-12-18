<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\Controller\CreateSite;
use App\Entity\Site;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[CoversClass(CreateSite::class)]
final class CreateSiteTest extends KernelTestCase
{
    private SiteRepository $siteRepository;

    private CreateSite $createSite;

    private FormView $formView;

    private FormFactoryInterface&MockObject $formFactory;

    private User $user;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine');
        assert($managerRegistry instanceof ManagerRegistry);

        $this->user = new User();
        $userRepository = $managerRegistry->getRepository(User::class);
        assert($userRepository instanceof UserRepository);
        $userRepository->save($this->user);

        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $container = new ServiceLocator([
            'form.factory' => fn () => $this->formFactory,
            'security.token_storage' => function () {
                $tokenStorage = $this->createMock(TokenStorageInterface::class);
                $token = $this->createMock(TokenInterface::class);

                $tokenStorage
                    ->expects(self::once())
                    ->method('getToken')
                    ->willReturn($token);

                $token
                    ->expects(self::once())
                    ->method('getUser')
                    ->willReturn($this->user);

                return $tokenStorage;
            },
        ]);

        $this->formView = new FormView();
        $this->siteRepository = new SiteRepository($managerRegistry);
        $this->createSite = new CreateSite($this->siteRepository);
        $this->createSite->setContainer($container);
    }

    public function testAttributes(): void
    {
        $ref = new ReflectionClass(CreateSite::class);

        $attributes = $ref->getAttributes();
        self::assertSame($attributes[0]->getName(), AsController::class);
        self::assertSame($attributes[1]->getName(), Route::class);
        self::assertSame($attributes[1]->getArguments(), ['path' => CreateSite::ROUTE_PATH, 'name' => CreateSite::ROUTE_NAME]);

        $refMethod = $ref->getMethod('__invoke');
        $attributes = $refMethod->getAttributes();

        self::assertSame($attributes[0]->getName(), Template::class);
        self::assertSame($attributes[0]->getArguments(), [CreateSite::TEMPLATE_NAME]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithValidForm(): void
    {
        $request = new Request();
        $site = new Site();
        $site->setName('Test Site');

        $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::once())
            ->method('handleRequest')
            ->willReturnSelf();

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('getData')
            ->willReturn($site);

        $form
            ->expects(self::once())
            ->method('createView')
            ->willReturn($this->formView);

        $this->formFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $response = ($this->createSite)($request);
        $all = $this->siteRepository->findAll();

        self::assertSame(['form' => $this->formView], $response);
        self::assertCount(1, $all);
        self::assertSame([$site], $all);
        self::assertSame('Test Site', $site->getName());
        self::assertSame('test-site', $site->getSlug());

        foreach ($all as $dbSite) {
            self::assertCount(1, $dbSite->getUserAccess());

            foreach ($dbSite->getUserAccess() as $userAccess) {
                self::assertSame($this->user, $userAccess->getUser());
                self::assertSame(UserRole::ROLE_ADMIN, $userAccess->getRole());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithInvalidForm(): void
    {
        $request = new Request();

        $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::once())
            ->method('handleRequest')
            ->willReturnSelf();

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $form
            ->expects(self::once())
            ->method('createView')
            ->willReturn($this->formView);

        $this->formFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $response = ($this->createSite)($request);

        self::assertSame(['form' => $this->formView], $response);
        self::assertCount(0, $this->siteRepository->findAll());
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithUnsubmittedForm(): void
    {
        $request = new Request();

        $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::once())
            ->method('handleRequest')
            ->willReturnSelf();

        $form
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(false);

        $form
            ->expects(self::once())
            ->method('createView')
            ->willReturn($this->formView);

        $this->formFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($form);

        $response = ($this->createSite)($request);

        self::assertSame(['form' => $this->formView], $response);
        self::assertCount(0, $this->siteRepository->findAll());
    }
}
