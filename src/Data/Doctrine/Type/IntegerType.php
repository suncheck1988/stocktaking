<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use App\Application\ValueObject\IntegerValueObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class IntegerType extends \Doctrine\DBAL\Types\IntegerType
{
    public const NAME = '';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $className = $this->getClassName();
        return $value instanceof $className && $value instanceof IntegerValueObject ? $value->getValue() : $value;
    }

    /**
     * @psalm-suppress InvalidCast
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidStringClass
     *
     * @param mixed $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $className = $this->getClassName();
        return $value !== null ? new $className((int)$value) : null;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        $name = static::NAME;
        \assert(\is_string($name) && $name !== '');
        return $name;
    }

    abstract protected function getClassName(): string;
}
