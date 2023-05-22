<?php

declare(strict_types=1);

namespace App\Auth\Command\Employee\Update;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    public function __construct(
        #[NotBlank]
        private readonly string $employeeId,
        #[NotBlank]
        private readonly string $name,
        private readonly bool $isFinanciallyResponsiblePerson
    ) {
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isFinanciallyResponsiblePerson(): bool
    {
        return $this->isFinanciallyResponsiblePerson;
    }
}
