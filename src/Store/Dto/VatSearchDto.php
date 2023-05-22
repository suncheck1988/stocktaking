<?php

declare(strict_types=1);

namespace App\Store\Dto;

class VatSearchDto
{
    public ?string $clientId = null;
    public bool $isDefault = false;
}
