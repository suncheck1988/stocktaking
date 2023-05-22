<?php

declare(strict_types=1);

namespace App\Store\Specification\FixedAsset;

use App\Application\Exception\DomainException;
use App\Client\Model\Employee\Employee;
use App\Store\Exception\FixedAsset\FixedAssetInventoryNumberAlreadyExistException;
use App\Store\Exception\FixedAsset\FixedAssetSerialNumberAlreadyExistException;
use App\Store\Model\FixedAsset\FixedAsset;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class FixedAssetSpecification
{
    public function __construct(
        private readonly UniqueFixedAssetSerialNumberSpecification $uniqueFixedAssetSerialNumberSpecification,
        private readonly UniqueFixedAssetInventoryNumberSpecification $uniqueFixedAssetInventoryNumberSpecification
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function check(FixedAsset $fixedAsset, ?Employee $employee): void
    {
        if ($this->uniqueFixedAssetSerialNumberSpecification->isSatisfiedBy($fixedAsset) === false) {
            throw new FixedAssetSerialNumberAlreadyExistException($fixedAsset->getSerialNumber());
        }
        if ($this->uniqueFixedAssetInventoryNumberSpecification->isSatisfiedBy($fixedAsset) === false) {
            throw new FixedAssetInventoryNumberAlreadyExistException($fixedAsset->getInventoryNumber());
        }

        if (!$fixedAsset->getCategory()->getStatus()->isActive()) {
            throw new DomainException('Категория должна быть активна');
        }

        if ($fixedAsset->getCategory()->getParent() === null) {
            throw new DomainException('Категория должна входить в корневую категорию');
        }
        if (!empty($fixedAsset->getCategory()->getChildren())) {
            throw new DomainException('Категория не должна быть корневой');
        }

        if ($fixedAsset->getCounterparty() !== null && !$fixedAsset->getCounterparty()?->getClient()->getUser()->getStatus()->isActive()) {
            throw new DomainException('Поставщик должен быть активен');
        }

        if ($fixedAsset->getWarehouse() !== null && !$fixedAsset->getWarehouse()?->getStatus()->isActive()) {
            throw new DomainException('Склад должен быть активен');
        }

        if ($fixedAsset->getPurchasePrice()->toCurrency() < 1) {
            throw new DomainException('Закупочная цена должна быть больше нуля');
        }

        if (!$fixedAsset->getUnit()->getStatus()->isActive()) {
            throw new DomainException('Единица измерения должна быть активна');
        }

        if ($employee !== null && !$employee->getUser()->getStatus()->isActive()) {
            throw new DomainException('Материально ответственное лицо должно быть активным пользователем');
        }
        if ($employee !== null && !$employee->isFinanciallyResponsiblePerson()) {
            throw new DomainException(sprintf('Сотрудник %s не является материально ответственным лицом', $employee->getClient()->getUser()->getName()));
        }

        if ($fixedAsset->getVat() !== null && !$fixedAsset->getVat()?->getStatus()->isActive()) {
            throw new DomainException('Ставка НДС должна быть активна');
        }
    }
}
