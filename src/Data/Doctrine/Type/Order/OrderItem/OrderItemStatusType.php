<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Order\OrderItem;

use App\Data\Doctrine\Type\EnumType;
use App\Order\Model\Order\OrderItem\Status;

class OrderItemStatusType extends EnumType
{
    public const NAME = 'order_order_item_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
