<?php

declare(strict_types=1);

namespace App\Store\Exception\Vat;

use DomainException;

class VatAlreadyExistException extends DomainException
{
    public function __construct(int $value)
    {
        parent::__construct(
            sprintf(
                'НДС с значением %s уже существует',
                $value
            )
        );
    }
}
