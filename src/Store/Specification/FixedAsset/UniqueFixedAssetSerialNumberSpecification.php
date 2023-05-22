<?php

declare(strict_types=1);

namespace App\Store\Specification\FixedAsset;

use App\Store\Model\FixedAsset\FixedAsset;
use App\Store\Repository\FixedAssetRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueFixedAssetSerialNumberSpecification
{
    public function __construct(
        private readonly FixedAssetRepository $fixedAssetRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(FixedAsset $fixedAsset): bool
    {
        return !$this->fixedAssetRepository->existBySerialNumber($fixedAsset);
    }
}
