<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\FixedAsset;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\FixedAsset\Status;

class FixedAssetStatusType extends EnumType
{
    public const NAME = 'store_fixed_asset_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
