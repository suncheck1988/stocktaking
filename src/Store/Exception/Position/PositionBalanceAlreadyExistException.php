<?php

declare(strict_types=1);

namespace App\Store\Exception\Position;

use App\Store\Model\Position\Position;
use App\Store\Model\Warehouse\Warehouse;
use DomainException;

class PositionBalanceAlreadyExistException extends DomainException
{
    public function __construct(Position $position, Warehouse $warehouse)
    {
        parent::__construct(
            sprintf(
                'Баланс позиции %s на складе %s уже существует',
                $position->getName(),
                $warehouse->getName()
            )
        );
    }
}
