<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\ValueObject\StringValueObject;
use Assert\Assertion;

final class Email extends StringValueObject
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        Assertion::email($this->value, 'Не действительный адрес электронной почты');
    }
}
