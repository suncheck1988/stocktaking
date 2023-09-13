<?php

declare(strict_types=1);

namespace App\Store\Dto;

class VatSearchDto
{
    public ?string $id = null;
    public ?bool $isDefault = null;
    public ?int $status = null;
}
