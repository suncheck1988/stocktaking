<?php

declare(strict_types=1);

namespace App\Client\Command\Employee\Block;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Client\Repository\EmployeeRepository;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly EmployeeRepository $employeeRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $employee = $this->employeeRepository->get(new Uuid($command->getEmployeeId()), $client);

        $employee->getUser()->block();

        $this->flusher->flush();
    }
}
