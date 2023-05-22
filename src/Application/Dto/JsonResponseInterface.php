<?php

declare(strict_types=1);

namespace App\Application\Dto;

use JsonSerializable;

interface JsonResponseInterface extends JsonSerializable
{
    /**
     * @psalm-suppress MissingParamType
     */
    public static function fromModel($model): self;
}
