<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Twig\Extension;

use App\Twig\Extension\UiExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

#[CoversClass(UiExtension::class)]
final class UiExtensionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetFunctions(): void
    {
        $extension = new UiExtension($this->createMock(RouterInterface::class), $this->createMock(FormFactoryInterface::class));

        self::assertCount(1, $extension->getFunctions());

        foreach ($extension->getFunctions() as $function) {
            self::assertInstanceOf(TwigFunction::class, $function);
        }
    }

    /**
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testDeleteBtn(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $form = $this->createMock(FormInterface::class);

        $formView = new FormView();
        $formView->vars['attr']['label'] = 'test';

        $router
            ->expects(self::once())
            ->method('generate')
            ->with('test', ['id' => 'test'])
            ->willReturn('/test');

        $formFactory
            ->expects(self::once())
            ->method('createBuilder')
            ->with(FormType::class)
            ->willReturn($formBuilder);

        $formBuilder
            ->expects(self::once())
            ->method('setAction')
            ->with('/test')
            ->willReturn($formBuilder)
        ;

        $formBuilder
            ->expects(self::once())
            ->method('setMethod')
            ->with('DELETE')
            ->willReturn($formBuilder)
        ;

        $formBuilder
            ->expects(self::once())
            ->method('add')
            ->with('submit', SubmitType::class, ['label' => 'test', 'attr' => ['class' => 'btn-danger']])
            ->willReturn($formBuilder)
        ;

        $formBuilder
            ->expects(self::once())
            ->method('getForm')
            ->willReturn($form)
        ;

        $form
            ->expects(self::once())
            ->method('createView')
            ->willReturn($formView);

        $extension = new UiExtension($router, $formFactory);

        $string = $extension->deleteBtn(new Environment(new ArrayLoader(['ui/button/delete.html.twig' => '<button>{{ form.vars.attr.label }}</button>'])), 'test', ['id' => 'test'], 'test');

        self::assertSame('<button>test</button>', $string);
    }
}
