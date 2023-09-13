<?php

declare(strict_types=1);

namespace App\Client\Command\Employee\Active;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $employeeId
    ) {
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }
}
