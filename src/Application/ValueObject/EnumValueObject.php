<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

use Assert\Assertion;
use Assert\AssertionFailedException;
use ReflectionClass;

abstract class EnumValueObject extends IntegerValueObject
{
    private static array $values = [];

    /**
     * @throws AssertionFailedException
     */
    public function __construct(int $value)
    {
        parent::__construct($value);
        Assertion::inArray($this->value, self::getValues(), 'Incorrect value ' . static::class . ' ' . $this->value);
    }

    /**
     * @psalm-suppress UnsafeInstantiation
     * @throws AssertionFailedException
     */
    public static function __callStatic(string $name, array $arguments): static
    {
        $value = strtoupper($name);
        return new static((int)\constant(static::class . '::' . $value));
    }

    /**
     * @return array<int, string>
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedArgumentTypeCoercion
     * @psalm-suppress MixedInferredReturnType
     */
    public static function getNames(): array
    {
        if (!isset(self::$values[static::class])) {
            $constants = (new ReflectionClass(static::class))->getConstants();
            self::$values[static::class] = array_combine($constants, array_keys($constants));
        }

        return self::$values[static::class];
    }

    /**
     * @return int[]
     */
    public static function getValues(): array
    {
        return array_keys(self::getNames());
    }

    public function getName(): string
    {
        return static::getNames()[$this->getValue()];
    }
}
