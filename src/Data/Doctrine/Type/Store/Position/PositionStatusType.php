<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\Position;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\Position\Status;

class PositionStatusType extends EnumType
{
    public const NAME = 'store_position_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
