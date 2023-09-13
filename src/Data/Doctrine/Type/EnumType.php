<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends IntegerType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'SMALLINT';
    }
}
