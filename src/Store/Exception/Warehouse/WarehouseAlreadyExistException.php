<?php

declare(strict_types=1);

namespace App\Store\Exception\Warehouse;

use DomainException;

class WarehouseAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Склад с названием %s уже существует',
                $name
            )
        );
    }
}
