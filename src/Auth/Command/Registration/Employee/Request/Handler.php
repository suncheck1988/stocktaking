<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Employee\Request;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Model\User\Role;
use App\Auth\Model\User\User;
use App\Auth\Model\User\UserEmailConfirm\Type;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\PasswordGenerator;
use App\Auth\Service\Auth\RegistrationConfirmEmailSender;
use App\Auth\Service\User\UserPermissionUpdater;
use App\Auth\Specification\Auth\ClientRegistrationEmailConfirmSpecification;
use App\Auth\Specification\UniqueUserEmailSpecification;
use App\Client\Model\Employee\Employee;
use App\Client\Repository\ClientRepository;
use App\Client\Repository\EmployeeRepository;
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
        private readonly EmployeeRepository $employeeRepository,
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
        $client = $this->clientRepository->get(new Uuid($command->getClientUserId()));

        $date = new DateTimeImmutable();

        $user = new User(
            Uuid::generate(),
            $command->getName(),
            new Email($command->getEmail()),
            Role::client_employee(),
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

        $this->userPermissionUpdater->update($user, $command->getPermissions());

        $this->userRepository->add($user);

        $employee = new Employee(
            $user,
            $client,
            $command->isFinanciallyResponsiblePerson(),
            new DateTimeImmutable()
        );

        $this->employeeRepository->add($employee);

        $this->flusher->flush();

        $this->registrationConfirmEmailSender->send($user, $userEmailConfirm->getToken());
    }
}
