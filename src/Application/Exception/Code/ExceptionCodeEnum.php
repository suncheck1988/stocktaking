<?php

declare(strict_types=1);

namespace App\Application\Exception\Code;

enum ExceptionCodeEnum: int
{
    case EMAIL_CONFIRM_EXPIRED = 1;
    case PASSWORD_INCORRECT = 2;
}
