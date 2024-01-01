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

use App\Entity\User;
use App\Entity\UserInvite;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<User>
 */
final class UserInviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                    'class' => UserRole::class,
                    'required' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
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

    public function getBlockPrefix(): string
    {
        return 'user_invite';
    }
}
