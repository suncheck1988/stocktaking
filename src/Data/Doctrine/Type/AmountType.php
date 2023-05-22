<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use App\Application\ValueObject\Amount;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AmountType extends IntegerType
{
    public const NAME = 'amount';

    protected function getClassName(): string
    {
        return Amount::class;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'BIGINT';
    }
}
