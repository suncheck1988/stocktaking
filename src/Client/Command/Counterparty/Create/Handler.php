<?php

declare(strict_types=1);

namespace App\Client\Command\Counterparty\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Email;
use App\Auth\Service\AuthContext;
use App\Client\Model\Counterparty\Counterparty;
use App\Client\Repository\CounterpartyRepository;
use App\Data\Flusher;
use Assert\AssertionFailedException;
use DateTimeImmutable;

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
        $client = $this->authContext->getClient();

        $email = $command->getEmail();

        $counterparty = new Counterparty(
            Uuid::generate(),
            $client,
            $command->getName(),
            $email !== null ? new Email($email) : null,
            new DateTimeImmutable(),
        );

        $this->counterpartyRepository->add($counterparty);

        $this->flusher->flush();
    }
}
