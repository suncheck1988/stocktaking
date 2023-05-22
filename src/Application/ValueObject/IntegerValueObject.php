<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

abstract class IntegerValueObject extends AbstractValueObject
{
    protected int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isEqualTo(self $value): bool
    {
        return $this->value === $value->getValue();
    }
}
