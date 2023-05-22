<?php

declare(strict_types=1);

namespace App\Client\Command\Counterparty\Active;

use App\Application\ValueObject\Uuid;
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

        $counterparty->active();

        $this->flusher->flush();
    }
}
