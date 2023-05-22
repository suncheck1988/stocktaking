<?php

declare(strict_types=1);

namespace App\Store\Specification\Unit;

use App\Store\Model\Unit\Unit;
use App\Store\Repository\UnitRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueUnitNameSpecification
{
    public function __construct(
        private readonly UnitRepository $unitRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Unit $unit): bool
    {
        return !$this->unitRepository->existByName($unit);
    }
}
