<?php

declare(strict_types=1);

namespace App\Store\Exception\FixedAsset;

use DomainException;

class FixedAssetInventoryNumberAlreadyExistException extends DomainException
{
    public function __construct(string $inventoryNumber)
    {
        parent::__construct(
            sprintf(
                'Основное средство с инвентарным номером %s уже существует',
                $inventoryNumber
            )
        );
    }
}
