<?php

declare(strict_types=1);

namespace App\Order\Command\PaymentMethod\Update;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Order\Repository\PaymentMethodRepository;
use App\Order\Specification\PaymentMethod\UniquePaymentMethodNameSpecification;
use Assert\AssertionFailedException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly UniquePaymentMethodNameSpecification $uniquePaymentMethodNameSpecification,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $paymentMethod = $this->paymentMethodRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $paymentMethod->update($command->getName(), $this->uniquePaymentMethodNameSpecification);

        $this->flusher->flush();
    }
}
