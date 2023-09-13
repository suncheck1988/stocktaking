<?php

declare(strict_types=1);

namespace App\Order\Command\Order\Cancel;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Repository\OrderRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly OrderRepository $orderRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $order = $this->orderRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $order->canceled();

        $this->flusher->flush();
    }
}
