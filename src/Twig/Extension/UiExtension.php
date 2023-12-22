<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Twig\Extension;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function file_get_contents;

final class UiExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouterInterface      $router,
        private readonly FormFactoryInterface $formFactory,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('deleteBtn', $this->deleteBtn(...), ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('icon', $this->icon(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @throws SyntaxError | RuntimeError | LoaderError
     */
    public function deleteBtn(Environment $twig, string $url, array $parameters = [], string $label = 'Delete', string $class = 'btn-danger'): string
    {
        $form = $this->formFactory->createBuilder()
            ->setAction($this->router->generate($url, $parameters))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => $label, 'attr' => ['class' => $class]])
            ->getForm();

        return $twig->render('ui/button/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function icon(string $name): string
    {
        static $icons = [];

        if (! isset($icons[$name])) {
            $icons[$name] = file_get_contents($this->projectDir . '/src/Resources/icons/' . $name . '.svg');
        }

        return $icons[$name];
    }
}
