<?php

declare(strict_types=1);

namespace App\Auth\Specification;

use App\Auth\Model\User\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueUserEmailSpecification
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(User $user): bool
    {
        return !$this->userRepository->existByEmail($user->getEmail(), $user->getId());
    }
}
