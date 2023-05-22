<?php

declare(strict_types=1);

namespace App\Client\Service\Client;

use App\Auth\Model\User\User;
use App\Client\Model\Client\Client;
use App\Client\Repository\ClientRepository;
use App\Client\Repository\EmployeeRepository;

class ClientFinder
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly EmployeeRepository $employeeRepository
    ) {
    }

    public function findByUser(User $user): Client
    {
        if ($user->getRole()->isClient()) {
            return $this->clientRepository->get($user->getId());
        } else {
            return $this->employeeRepository->getById($user->getId())->getClient();
        }
    }
}
