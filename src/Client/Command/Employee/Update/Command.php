<?php

declare(strict_types=1);

namespace App\Client\Command\Employee\Update;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    /**
     * @param int[] $permissions
     */
    public function __construct(
        #[NotBlank]
        private readonly string $employeeId,
        #[NotBlank]
        private readonly string $name,
        private readonly bool $isFinanciallyResponsiblePerson,
        private readonly array $permissions
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

    /**
     * @return int[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
