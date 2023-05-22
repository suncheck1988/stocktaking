<?php

declare(strict_types=1);

namespace App\Auth\Command\Login\Request;

use App\Auth\Repository\UserRepository;
use App\Auth\Service\AuthTokenManager;
use App\Auth\Service\JWTPayloadGenerator;
use Doctrine\ORM\NonUniqueResultException;

class Handler
{
    public function __construct(
        private readonly UserRepository   $userRepository,
        private readonly AuthTokenManager $authTokenManager,
        private readonly JWTPayloadGenerator $jwtPayloadGenerator
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function handle(Command $command): string
    {
        $user = $this->userRepository->getActiveByEmail($command->getEmail());
        $user->checkPassword($command->getPassword());

        return $this->authTokenManager->encode(
            $this->jwtPayloadGenerator->generate((string)$user->getId())
        );
    }
}
