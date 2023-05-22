<?php

declare(strict_types=1);

namespace App\Auth\Dto;

use DateTimeImmutable;

class UserSearchDto
{
    public ?string $id = null;
    public ?string $name = null;

    public ?int $role = null;
    public ?int $status = null;
    public ?int $restaurantId = null;

    public ?DateTimeImmutable $createdAtStart = null;
    public ?DateTimeImmutable $createdAtEnd = null;
}
