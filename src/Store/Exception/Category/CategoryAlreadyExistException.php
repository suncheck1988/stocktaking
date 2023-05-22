<?php

declare(strict_types=1);

namespace App\Store\Exception\Category;

use DomainException;

class CategoryAlreadyExistException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Категория с названием %s уже существует',
                $name
            )
        );
    }
}
