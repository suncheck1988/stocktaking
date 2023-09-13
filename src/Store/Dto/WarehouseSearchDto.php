<?php

declare(strict_types=1);

namespace App\Store\Dto;

class WarehouseSearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?int $status = null;
    public ?string $withoutBalanceByPositionId = null;
}
