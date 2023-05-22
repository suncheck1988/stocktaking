<?php

declare(strict_types=1);

namespace App\Data\Doctrine\Type\Store\Category;

use App\Data\Doctrine\Type\EnumType;
use App\Store\Model\Category\Status;

class CategoryStatusType extends EnumType
{
    public const NAME = 'store_category_status';

    protected function getClassName(): string
    {
        return Status::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
