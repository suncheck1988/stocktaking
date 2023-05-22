<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Client\Confirm;

use App\Application\Exception\DomainException;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\UserEmailConfirm\Status;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;

class Handler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $token = new Uuid($command->getToken());

        $user = $this->userRepository->getNewByEmailConfirmToken($token);
        if ($user->getUserEmailConfirmByStatus(Status::confirmed(), Type::registration())) {
            throw new DomainException('Учётная запись уже активирована');
        }

        $userEmailConfirm = $user->getUserEmailConfirmByToken($token, Type::registration());
        if ($userEmailConfirm === null) {
            throw new DomainException('Подтверждение электронной почты для указанной ссылки не найдено');
        }
        if ($userEmailConfirm->isExpired()) {
            $userEmailConfirm->expired();

            $this->flusher->flush();

            throw new DomainException('Срок действия ссылки для подтверждения электронной почты истек');
        }

        $user->active();
        $userEmailConfirm->confirmed();

        $this->flusher->flush();
    }
}
