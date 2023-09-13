<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

abstract class AbstractValueObject
{
    public function getPropertyPath(): ?string
    {
        return null;
    }
}
