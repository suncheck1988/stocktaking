<?php

declare(strict_types=1);

namespace App\Order\Exception\DeliveryType;

use DomainException;

class DeliveryTypeAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Способ доставки с названием %s уже существует',
                $name
            )
        );
    }
}
