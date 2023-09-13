<?php

declare(strict_types=1);

namespace App\Order\Exception\PaymentMethod;

use DomainException;

class PaymentMethodAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Тип оплаты с названием %s уже существует',
                $name
            )
        );
    }
}
