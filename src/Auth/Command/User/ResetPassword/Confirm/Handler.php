<?php

declare(strict_types=1);

namespace App\Auth\Command\User\ResetPassword\Confirm;

use App\Application\Exception\DomainException;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\PasswordGenerator;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use Exception;

class Handler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordGenerator $passwordGenerator,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(Command $command): void
    {
        $token = new Uuid($command->getToken());

        $user = $this->userRepository->getActiveByEmailConfirmToken($token);

        $userEmailConfirm = $user->getUserEmailConfirmByToken($token, Type::password_reset());
        if ($userEmailConfirm === null) {
            throw new DomainException('Подтверждение сброса пароля для указанной ссылки не найдено');
        }
        if ($userEmailConfirm->getStatus()->isConfirmed()) {
            throw new DomainException('Доступ к учетной записи уже восстановлена');
        }
        if ($userEmailConfirm->isExpired()) {
            $userEmailConfirm->expired();

            $this->flusher->flush();

            throw new DomainException('Срок действия ссылки для подтверждения сброса пароля истек, запросите сброс пароля повторно');
        }

        $user->changePassword($this->passwordGenerator->getHashByPasswordString($command->getPassword()));
        $userEmailConfirm->confirmed();

        $this->flusher->flush();
    }
}
