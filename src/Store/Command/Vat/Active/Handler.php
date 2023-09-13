<?php

declare(strict_types=1);

namespace App\Store\Command\Vat\Active;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\VatRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly VatRepository $vatRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $unit = $this->vatRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $unit->active();

        $this->flusher->flush();
    }
}
