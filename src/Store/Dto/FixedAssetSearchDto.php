<?php

declare(strict_types=1);

namespace App\Store\Dto;

class FixedAssetSearchDto
{
    public ?string $id = null;
    public ?string $categoryId = null;
    public ?string $warehouseId = null;
    public ?string $name = null;
    public ?string $serialNumber = null;
    public ?int $status = null;
}
