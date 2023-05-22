<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

use Assert\Assertion;
use Assert\AssertionFailedException;

final class Phone extends StringValueObject
{
    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $value)
    {
        parent::__construct($value);
        Assertion::regex($this->value, '/^\+?\d+$/', 'Некорректный номер телефона (' . $this->value . ')');
    }
}
