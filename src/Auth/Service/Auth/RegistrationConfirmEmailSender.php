<?php

declare(strict_types=1);

namespace App\Auth\Service\Auth;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\User;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as MimeEmail;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegistrationConfirmEmailSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function send(User $user, Uuid $token): void
    {
        try {
            $message = (new MimeEmail())
                ->subject('Подтверждение электронной почты')
                ->to($user->getEmail()->getValue())
                ->html(
                    $this->twig->render(
                        'auth/registration/confirm.html.twig',
                        [
                            'token' => $token->getValue(),
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new Exception($e->getMessage());
        }
    }
}
