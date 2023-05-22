<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Common\RecreateEmailConfirm;

use App\Application\Exception\DomainException;
use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\UserEmailConfirm\Status;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\RegistrationConfirmEmailSender;
use App\Auth\Specification\Auth\ClientRegistrationEmailConfirmSpecification;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Handler
{
    public function __construct(
        private readonly UserRepository                              $userRepository,
        private readonly ClientRegistrationEmailConfirmSpecification $clientRegistrationEmailConfirmSpecification,
        private readonly RegistrationConfirmEmailSender              $registrationConfirmEmailSender,
        private readonly Flusher                                     $flusher
    ) {
    }

    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress TypeDoesNotContainType
     *
     * @throws NonUniqueResultException
     * @throws AssertionFailedException
     * @throws NoResultException
     * @throws TransportExceptionInterface
     */
    public function handle(Command $command): void
    {
        $token = new Uuid($command->getToken());

        $user = $this->userRepository->getNewByEmailConfirmToken($token);
        if ($user->getUserEmailConfirmByStatus(Status::confirmed(), Type::registration())) {
            throw new DomainException('Учётная запись уже активирована');
        }

        $item = $user->getUserEmailConfirmByStatus(Status::new(), Type::registration());
        if ($item !== null) {
            throw new DomainException('Письмо с подтверждением уже отправлено');
        }
        if ($item !== null && $item->isExpired()) {
            $item->expired();
        }

        $userEmailConfirm = $user->addUserEmailConfirm(
            Uuid::generate(),
            Uuid::generate(),
            Type::registration(),
            new DateTimeImmutable(),
            $this->clientRegistrationEmailConfirmSpecification
        );

        $this->flusher->flush();

        $this->registrationConfirmEmailSender->send($user, $userEmailConfirm->getToken());
    }
}
