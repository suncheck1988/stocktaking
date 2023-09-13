<?php

declare(strict_types=1);

namespace App\Order\Dto;

class PaymentMethodSearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?int $status = null;
}
