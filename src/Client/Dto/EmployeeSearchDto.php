<?php

declare(strict_types=1);

namespace App\Client\Dto;

class EmployeeSearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?int $status = null;
}
