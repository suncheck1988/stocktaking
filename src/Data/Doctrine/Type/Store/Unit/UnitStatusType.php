<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\Unit;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\Unit\Status;

class UnitStatusType extends EnumType
{
    public const NAME = 'store_unit_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
