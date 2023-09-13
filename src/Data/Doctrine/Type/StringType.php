<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use App\Application\ValueObject\StringValueObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class StringType extends \Doctrine\DBAL\Types\StringType
{
    public const NAME = '';

    abstract protected function getClassName(): string;

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $className = $this->getClassName();
        return $value instanceof $className && $value instanceof StringValueObject ? $value->getValue() : $value;
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $className = $this->getClassName();
        return $value !== null ? new $className((string)$value) : null;
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
}
