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
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function assert;
use function file_exists;
use function file_get_contents;
use function iterator_to_array;

/**
 * @see \App\Tests\Twig\Extension\UiExtensionTest
 */
final class UiExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouterInterface      $router,
        private readonly FormFactoryInterface $formFactory,
        #[Autowire('%kernel.project_dir%')]
        private readonly string               $projectDir,
    ) {}

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('deleteBtn', $this->deleteBtn(...), ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('icon', $this->icon(...), ['is_safe' => ['html']]),
            new TwigFunction('uuid', static fn(): UuidV4 => Uuid::v4()),
        ];
    }

    /**
     * @return list<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_phone', $this->formatPhone(...)),
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

        if (!isset($icons[$name])) {
            $iconsPath = $this->projectDir . '/src/Resources/icons/';

            if (!file_exists($iconsPath . $name . '.svg')) {

                $allIcons = [];

                foreach (new DirectoryIterator($iconsPath) as $fileInfo) {
                    /** @var DirectoryIterator $fileInfo */
                    if ($fileInfo->isDot() || !$fileInfo->isFile()) {
                        continue;
                    }

                    $allIcons[] = $fileInfo->getFileInfo()->getBasename(".svg");
                }

                throw new \InvalidArgumentException(sprintf('Icon "%s" not found. Available icons: %s', $name, implode(', ', $allIcons)));
            }

            $icons[$name] = file_get_contents($iconsPath . $name . '.svg');
        }

        return $icons[$name];
    }

    /**
     * @throws NumberParseException
     */
    public function formatPhone(string $number): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $phone = $phoneUtil->parse($number);
        assert($phone instanceof PhoneNumber);

        return $phoneUtil->format($phone, PhoneNumberFormat::NATIONAL);
    }
}
