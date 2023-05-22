<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use App\Application\ValueObject\Uuid;
use Assert\AssertionFailedException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

final class UuidType extends GuidType
{
    public const NAME = 'uuid';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Uuid ? $value->getValue() : $value;
    }

    /**
     * @throws AssertionFailedException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uuid
    {
        return $value !== null ? new Uuid((string)$value) : null;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        \assert(\is_string(self::NAME));
        return self::NAME;
    }
}
