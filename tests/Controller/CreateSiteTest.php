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
use App\Repository\SiteRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CreateSite::class)]
final class CreateSiteTest extends KernelTestCase
{
    private SiteRepository $siteRepository;

    private CreateSite $createSite;

    private FormView $formView;

    private FormFactoryInterface&MockObject $formFactory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine');
        assert($managerRegistry instanceof ManagerRegistry);

        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $this->formView = new FormView();
        $this->siteRepository = new SiteRepository($managerRegistry);
        $this->createSite = new CreateSite($this->siteRepository);

        $this->createSite->setContainer($container);

        $container
            ->expects(self::once())
            ->method('get')
            ->with('form.factory')
            ->willReturn($this->formFactory);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithValidForm(): void
    {
        $request = new Request();
        $site = new Site();

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

        $response = $this->createSite->__invoke($request);
        $all = $this->siteRepository->findAll();

        self::assertSame(['form' => $this->formView], $response);
        self::assertCount(1, $all);
        self::assertSame([$site], $all);
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

        $response = $this->createSite->__invoke($request);

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

        $response = $this->createSite->__invoke($request);

        self::assertSame(['form' => $this->formView], $response);
        self::assertCount(0, $this->siteRepository->findAll());
    }
}
