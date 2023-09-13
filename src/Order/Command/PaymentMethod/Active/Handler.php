<?php

declare(strict_types=1);

namespace App\Order\Command\PaymentMethod\Active;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Repository\PaymentMethodRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $paymentMethod = $this->paymentMethodRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $paymentMethod->active();

        $this->flusher->flush();
    }
}
