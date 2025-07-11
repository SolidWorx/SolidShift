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

use DirectoryIterator;
use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use function assert;
use function file_exists;
use function file_get_contents;
use function Symfony\Component\String\s;

/**
 * @see \App\Tests\Twig\Extension\UiExtensionTest
 */
final readonly class UiExtension
{
    public function __construct(
        private RouterInterface $router,
        private FormFactoryInterface $formFactory,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    #[AsTwigFunction('uuid')]
    public function uuid(): UuidV4
    {
        return Uuid::v4();
    }

    /**
     * @param array<string, mixed> $parameters
     * @throws SyntaxError | RuntimeError | LoaderError
     */
    #[AsTwigFunction(name: 'deleteBtn', needsEnvironment: true, isSafe: ['html'])]
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

    #[AsTwigFunction('icon', isSafe: ['html'])]
    public function icon(string $name): string
    {
        static $icons = [];

        if (! isset($icons[$name])) {
            $iconsPath = $this->projectDir . '/src/Resources/icons/';

            if (! file_exists($iconsPath . $name . '.svg')) {
                $allIcons = [];

                foreach (new DirectoryIterator($iconsPath) as $fileInfo) {
                    /** @var DirectoryIterator $fileInfo */
                    if ($fileInfo->isDot()) {
                        continue;
                    }

                    if (! $fileInfo->isFile()) {
                        continue;
                    }

                    $allIcons[] = $fileInfo->getFileInfo()->getBasename('.svg');
                }

                throw new InvalidArgumentException(sprintf('Icon "%s" not found. Available icons: %s', $name, implode(', ', $allIcons)));
            }

            $icons[$name] = file_get_contents($iconsPath . $name . '.svg');

            if (false === $icons[$name]) {
                throw new RuntimeException(sprintf('Failed to read icon "%s"', $name));
            }

            $icons[$name] = s($icons[$name])->collapseWhitespace();
        }

        return $icons[$name];
    }

    /**
     * @throws NumberParseException
     */
    #[AsTwigFilter('format_phone')]
    public function formatPhone(string $number): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $phone = $phoneUtil->parse($number);
        assert($phone instanceof PhoneNumber);

        return $phoneUtil->format($phone, PhoneNumberFormat::NATIONAL);
    }
}
