<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function __invoke(
        #[Argument(description: 'Email address of the user to create', name: 'email')]
        string $email,
        #[Argument(description: 'Password of the user to create', name: 'password')]
        string $password,
        OutputInterface $output
    ): int {
        $user = new User()
            ->setUsername($email)
            ->setPassword($password);

        $this->userRepository->save($user);

        return Command::SUCCESS;
    }
}
