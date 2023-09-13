<?php

declare(strict_types=1);

namespace App\Auth\Command\User\ResetPassword\Request;

use App\Application\Exception\DomainException;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Model\User\UserEmailConfirm\Status;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\ResetPasswordConfirmEmailSender;
use App\Auth\Specification\Auth\ClientRegistrationEmailConfirmSpecification;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Handler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ClientRegistrationEmailConfirmSpecification $clientRegistrationEmailConfirmSpecification,
        private readonly ResetPasswordConfirmEmailSender $resetPasswordConfirmEmailSender,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function handle(Command $command): void
    {
        $user = $this->userRepository->getByEmail(new Email($command->getEmail()));
        if (!$user->getStatus()->isActive()) {
            throw new DomainException('Сброс пароля возможен только для активного пользователя');
        }

        if ($user->getUserEmailConfirmByStatus(Status::new(), Type::password_reset())) {
            throw new DomainException('Запрос на сброс пароля уже отправлен, проверьте свою электронную почту');
        }

        try {
            $userEmailConfirm = $user->addUserEmailConfirm(
                Uuid::generate(),
                Uuid::generate(),
                Type::password_reset(),
                new DateTimeImmutable(),
                $this->clientRegistrationEmailConfirmSpecification
            );
        } catch (AssertionFailedException|NoResultException|NonUniqueResultException $e) {
            throw new Exception();
        }

        $this->flusher->flush();

        $this->resetPasswordConfirmEmailSender->send($user, $userEmailConfirm->getToken());
    }
}
