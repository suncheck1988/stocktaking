<?php

declare(strict_types=1);

namespace App\Auth\Specification\Auth;

use App\Auth\Model\User\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class ClientRegistrationEmailConfirmSpecification
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isEmailConfirmRequested(User $user): bool
    {
        return !$this->userRepository->existByActualEmailConfirm($user);
    }
}
