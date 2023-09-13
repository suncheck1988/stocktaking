<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\Warehouse;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\Warehouse\Status;

class WarehouseStatusType extends EnumType
{
    public const NAME = 'store_warehouse_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
