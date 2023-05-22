<?php

declare(strict_types=1);

namespace App\Store\Exception\FixedAsset;

use DomainException;

class FixedAssetSerialNumberAlreadyExistException extends DomainException
{
    public function __construct(string $serialNumber)
    {
        parent::__construct(
            sprintf(
                'Основное средство с серийным номером %s уже существует',
                $serialNumber
            )
        );
    }
}
