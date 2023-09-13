<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Order\DeliveryType;

use App\Data\Doctrine\Type\EnumType;
use App\Order\Model\DeliveryType\Status;

class DeliveryTypeStatusType extends EnumType
{
    public const NAME = 'order_delivery_type_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
