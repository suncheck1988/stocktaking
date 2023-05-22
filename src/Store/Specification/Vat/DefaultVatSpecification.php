<?php

declare(strict_types=1);

namespace App\Store\Specification\Vat;

use App\Store\Model\Vat\Vat;
use App\Store\Repository\VatRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DefaultVatSpecification
{
    public function __construct(
        private readonly VatRepository $vatRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isExistDefault(Vat $vat): bool
    {
        return !$this->vatRepository->existDefault($vat);
    }
}
