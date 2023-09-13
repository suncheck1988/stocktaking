<?php

declare(strict_types=1);

namespace App\Store\Dto;

class CategorySearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?int $status = null;
    public bool $onlyRoot = false;
}
