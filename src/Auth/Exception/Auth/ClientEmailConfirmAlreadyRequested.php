<?php

declare(strict_types=1);

namespace App\Auth\Exception\Auth;

use DomainException;

class ClientEmailConfirmAlreadyRequested extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Письмо с подтверждением электронной почты уже отправлено'
        );
    }
}
