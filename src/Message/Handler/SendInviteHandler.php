<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Message\Handler;

use App\Controller\User\Invite\AcceptInvite;
use App\Entity\UserInvite;
use App\Message\SendInvite;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function sprintf;

#[AsMessageHandler(handles: SendInvite::class)]
final readonly class SendInviteHandler
{
    public function __construct(
        private MailerInterface       $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private NotifierInterface     $notifier,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendInvite $message): void
    {
        $this->sendEmailInvite($message->userInvite);
        $this->sendSmsInvite($message->userInvite);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendEmailInvite(UserInvite $userInvite): void
    {
        $email = $userInvite->getEmail();

        if (null === $email || '' === $email) {
            return;
        }

        $this->mailer->send(
            (new TemplatedEmail())
                ->from(new Address($userInvite->getSite()->getSlug() . '@solidshift.app', $userInvite->getSite()->getName()))
                ->to($email)
                ->subject('Invitation to join')
                ->text(
                    sprintf(
                        'You have been invited to join %s. Please click the following link to accept the invitation: %s',
                        $userInvite->getSite()->getName(),
                        $this->urlGenerator
                            ->generate(
                                AcceptInvite::ROUTE_NAME,
                                ['hash' => $userInvite->getHash()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            )
                    )
                )
        );
    }

    private function sendSmsInvite(UserInvite $userInvite): void
    {
        $phone = $userInvite->getPhone();

        if (null === $phone || '' === $phone) {
            return;
        }

        $notification = (new Notification('', ['sms']))
            ->content(
                sprintf(
                    'You have been invited to join %s. Please click the following link to accept the invitation: %s',
                    $userInvite->getSite()->getName(),
                    $this->urlGenerator
                        ->generate(
                            AcceptInvite::ROUTE_NAME,
                            ['hash' => $userInvite->getHash()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                )
            );

        $recipient = new Recipient(
            phone: $phone,
        );

        $this->notifier->send($notification, $recipient);
    }
}
