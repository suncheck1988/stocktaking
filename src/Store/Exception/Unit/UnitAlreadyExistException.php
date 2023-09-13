<?php

declare(strict_types=1);

namespace App\Store\Exception\Unit;

use DomainException;

class UnitAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Единица измерения с названием %s уже существует',
                $name
            )
        );
    }
}
