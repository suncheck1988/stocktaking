<?php

declare(strict_types=1);

namespace App\Order\Specification\PaymentMethod;

use App\Order\Model\PaymentMethod\PaymentMethod;
use App\Order\Repository\PaymentMethodRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniquePaymentMethodNameSpecification
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethodRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(PaymentMethod $paymentMethod): bool
    {
        return !$this->paymentMethodRepository->existByName($paymentMethod);
    }
}
