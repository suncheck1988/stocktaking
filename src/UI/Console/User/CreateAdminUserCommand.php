<?php

declare(strict_types=1);

namespace App\UI\Console\User;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Auth\Model\User\User;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\Auth\PasswordGenerator;
use App\Auth\Service\User\UserPermissionUpdater;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPermissionUpdater $userPermissionUpdater,
        private readonly PasswordGenerator $passwordGenerator,
        private readonly Flusher $flusher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('user:create-admin-user');
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new DateTimeImmutable();

        try {
            $user = new User(
                Uuid::generate(),
                'Администратор',
                new Email('admin@stocktaking.ru'),
                Role::admin(),
                $date,
                null
            );
        } catch (AssertionFailedException|NoResultException|NonUniqueResultException $e) {
            throw new Exception($e->getMessage());
        }

        $user->active();

        $user->createUserAuth(
            Uuid::generate(),
            $this->passwordGenerator->getHashByPasswordString('35cfda4a-3599-4c7f-8046-7e360e7d3020'),
            $date
        );

        $this->userPermissionUpdater->update($user, Permission::getValues());

        $this->userRepository->add($user);

        $this->flusher->flush();

        return self::SUCCESS;
    }
}
