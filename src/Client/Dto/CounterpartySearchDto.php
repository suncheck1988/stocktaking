<?php

declare(strict_types=1);

namespace App\Client\Dto;

class CounterpartySearchDto
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?int $status = null;
}
