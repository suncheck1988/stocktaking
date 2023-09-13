<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Order;

use App\Data\Doctrine\Type\EnumType;
use App\Order\Model\Order\Status;

class OrderStatusType extends EnumType
{
    public const NAME = 'order_order_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
