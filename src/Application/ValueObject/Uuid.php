<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

use Assert\Assertion;
use Assert\AssertionFailedException;

final class Uuid
{
    protected string $value;

    /**
     * @throws AssertionFailedException
     */
    public function __construct(string $value)
    {
        Assertion::uuid($value, 'Неверный UUID: ' . $value, $this->getPropertyPath());
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * @throws AssertionFailedException
     */
    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function getPropertyPath(): ?string
    {
        return null;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
