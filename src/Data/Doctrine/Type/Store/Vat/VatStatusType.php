<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\Vat;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\Vat\Status;

class VatStatusType extends EnumType
{
    public const NAME = 'store_vat_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
