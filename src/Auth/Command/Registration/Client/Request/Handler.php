<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Client\Request;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Auth\Model\User\User;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\PasswordGenerator;
use App\Auth\Service\Auth\RegistrationConfirmEmailSender;
use App\Auth\Service\User\UserPermissionUpdater;
use App\Auth\Specification\Auth\ClientRegistrationEmailConfirmSpecification;
use App\Auth\Specification\UniqueUserEmailSpecification;
use App\Client\Model\Client\Client;
use App\Client\Repository\ClientRepository;
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
        private readonly ClientRepository $clientRepository,
        private readonly UserPermissionUpdater $userPermissionUpdater,
        private readonly UniqueUserEmailSpecification $uniqueUserEmailSpecification,
        private readonly ClientRegistrationEmailConfirmSpecification $clientRegistrationEmailConfirmSpecification,
        private readonly PasswordGenerator $passwordGenerator,
        private readonly RegistrationConfirmEmailSender $registrationConfirmEmailSender,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws AssertionFailedException|NoResultException
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function handle(Command $command): void
    {
        $date = new DateTimeImmutable();

        $user = new User(
            Uuid::generate(),
            $command->getName(),
            new Email($command->getEmail()),
            Role::client(),
            $date,
            $this->uniqueUserEmailSpecification
        );

        $user->createUserAuth(
            Uuid::generate(),
            $this->passwordGenerator->getHashByPasswordString($command->getPassword()),
            $date
        );

        $userEmailConfirm = $user->addUserEmailConfirm(
            Uuid::generate(),
            Uuid::generate(),
            Type::registration(),
            $date,
            $this->clientRegistrationEmailConfirmSpecification
        );

        $this->userPermissionUpdater->update($user, Permission::getValues());

        $this->userRepository->add($user);

        $client = new Client($user, $date);

        $this->clientRepository->add($client);

        $this->flusher->flush();

        $this->registrationConfirmEmailSender->send($user, $userEmailConfirm->getToken());
    }
}
