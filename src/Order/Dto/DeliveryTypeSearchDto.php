<?php

declare(strict_types=1);

namespace App\Order\Dto;

class DeliveryTypeSearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?int $status = null;
}
