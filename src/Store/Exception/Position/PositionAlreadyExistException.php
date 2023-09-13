<?php

declare(strict_types=1);

namespace App\Store\Exception\Position;

use DomainException;

class PositionAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Позиция с названием %s уже существует',
                $name
            )
        );
    }
}
