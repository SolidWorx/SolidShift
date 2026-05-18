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

use App\Entity\Role;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserInvite;
use App\Enum\MembershipRole;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function assert;

/**
 * @extends AbstractType<User>
 */
final class UserInviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $site = $options['site'];
        assert($site instanceof Site);

        $organisation = $site->getOrganisation();
        $roles = $organisation !== null ? [...$organisation->getRoles()] : [];

        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'attr' => [
                        'placeholder' => 'test@example.com',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'phone',
                PhoneType::class,
                [
                    'label' => 'Mobile Number',
                    'required' => false,
                ]
            )
            ->add(
                'role',
                EnumType::class,
                [
                    'class' => MembershipRole::class,
                    'required' => true,
                ]
            )
            ->add('preAssignedRoles', EntityType::class, [
                'class' => Role::class,
                'choices' => $roles,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choice_label' => 'name',
                'label' => 'Pre-assigned job roles',
                'help' => 'Optional: job roles to attach to the user when they accept the invite.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['site']);
        $resolver->setAllowedTypes('site', Site::class);
        $resolver->setDefaults([
            'data_class' => UserInvite::class,
            'constraints' => [
                // Check that at least one of the fields is filled in
                new Assert\Callback(
                    static function (UserInvite $userInvite, ExecutionContextInterface $context): void {
                        if ($userInvite->getEmail() !== '' && $userInvite->getEmail() !== null) {
                            return;
                        }

                        if ($userInvite->getPhone() !== '' && $userInvite->getPhone() !== null) {
                            return;
                        }

                        $context->addViolation('Please enter either an email address or mobile number');
                    }
                ),
            ],
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'user_invite';
    }
}
