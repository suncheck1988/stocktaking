<?php

declare(strict_types=1);

namespace App\Client\Command\Counterparty\Update;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Service\AuthContext;
use App\Client\Repository\CounterpartyRepository;
use App\Data\Flusher;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly CounterpartyRepository $counterpartyRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $counterparty = $this->counterpartyRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $email = $command->getEmail();

        $counterparty->update(
            $command->getName(),
            $email !== null ? new Email($email) : null
        );

        $this->flusher->flush();
    }
}
