<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Area;
use App\Entity\Organisation;
use App\Entity\Role;
use App\Entity\ShiftRequirement;
use App\Entity\ShiftTemplate;
use App\Entity\Site;
use App\Repository\ShiftTemplateRepository;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Ulid;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use function assert;
use function is_array;
use function is_string;

/**
 * @extends AbstractType<ShiftRequirement>
 */
final class ShiftRequirementType extends AbstractType
{
    public function __construct(
        private readonly ShiftTemplateRepository $shiftTemplateRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $site = $options['site'];
        $organisation = $options['organisation'];
        assert($site instanceof Site);
        assert($organisation instanceof Organisation);

        $siteAreas = $site->getAreas();
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('template', EntityType::class, [
                'class' => ShiftTemplate::class,
                'choices' => $this->shiftTemplateRepository->findBy(['organisation' => $organisation], ['name' => 'ASC']),
                'required' => false,
                'placeholder' => 'Load from template…',
                'choice_label' => 'name',
                'autocomplete' => true,
                'label' => 'Shift template',
                'help' => 'Pick a saved shift template to fill the fields below. Values you have already set stay as-is.',
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choices' => $organisation->getRoles(),
                'choice_label' => 'name',
                'placeholder' => 'Select a role',
            ])
            ->addDependent('area', 'role', static function (DependentField $field, ?Role $role) use ($siteAreas): void {
                $allowedAreas = $role?->getAllowedAreas();
                $choices = $allowedAreas !== null && ! $allowedAreas->isEmpty()
                    ? $allowedAreas
                    : $siteAreas;

                $field->add(EntityType::class, [
                    'class' => Area::class,
                    'choices' => $choices,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'Any area',
                    'help' => $role !== null && $allowedAreas !== null && ! $allowedAreas->isEmpty()
                        ? sprintf('Limited to the areas %s is allowed in.', $role->getName())
                        : null,
                ]);
            })
            ->add('startTime', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'help' => "Leave blank to use the occurrence's start time",
            ])
            ->add('endTime', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'help' => "Leave blank to use the occurrence's end time",
            ])
            ->add('requiredMin', NumberType::class, [
                'required' => false,
                'help' => 'Minimum people needed',
            ])
            ->add('requiredMax', NumberType::class, [
                'required' => false,
                'help' => 'Maximum people allowed',
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->preFillFromTemplate(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ShiftRequirement::class]);
        $resolver->setRequired(['site', 'organisation']);
        $resolver->setAllowedTypes('site', Site::class);
        $resolver->setAllowedTypes('organisation', Organisation::class);
    }

    /**
     * When the user picks a shift template, pre-populate any empty fields with
     * the template's defaults so the row renders with the loaded values.
     * Existing user input wins — we only touch fields the user has not filled.
     */
    private function preFillFromTemplate(FormEvent $event): void
    {
        $data = $event->getData();

        if (! is_array($data)) {
            return;
        }

        $templateId = $data['template'] ?? null;

        if (! is_string($templateId) || $templateId === '') {
            return;
        }

        $template = $this->shiftTemplateRepository->find(Ulid::fromString($templateId));

        if (! $template instanceof ShiftTemplate) {
            return;
        }

        if (($data['role'] ?? '') === '' && $template->getRole() instanceof Role) {
            $data['role'] = $template->getRole()->getId()->toBase32();
        }

        if (($data['area'] ?? '') === '' && $template->getArea() instanceof Area) {
            $data['area'] = $template->getArea()->getId()->toBase32();
        }

        if (($data['startTime'] ?? '') === '' && $template->getStartTime() instanceof DateTimeImmutable) {
            $data['startTime'] = $template->getStartTime()->format('H:i');
        }

        if (($data['endTime'] ?? '') === '' && $template->getEndTime() instanceof DateTimeImmutable) {
            $data['endTime'] = $template->getEndTime()->format('H:i');
        }

        if (($data['requiredMin'] ?? '') === '' && $template->getRequiredMin() !== null) {
            $data['requiredMin'] = (string) $template->getRequiredMin();
        }

        if (($data['requiredMax'] ?? '') === '' && $template->getRequiredMax() !== null) {
            $data['requiredMax'] = (string) $template->getRequiredMax();
        }

        $event->setData($data);
    }
}
