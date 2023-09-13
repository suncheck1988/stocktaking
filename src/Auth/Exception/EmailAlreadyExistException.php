<?php

declare(strict_types=1);

namespace App\Auth\Exception;

use DomainException;

class EmailAlreadyExistException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            sprintf(
                'Пользователь с электронной почтой %s уже существует',
                $email
            )
        );
    }
}
