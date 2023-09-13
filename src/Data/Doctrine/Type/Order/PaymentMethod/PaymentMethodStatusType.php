<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Order\PaymentMethod;

use App\Data\Doctrine\Type\EnumType;
use App\Order\Model\PaymentMethod\Status;

class PaymentMethodStatusType extends EnumType
{
    public const NAME = 'order_payment_method_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
