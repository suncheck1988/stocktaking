<?php

declare(strict_types=1);

namespace App\Client\Command\Employee\ChangePermission;

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
        private readonly array $permissions
    ) {
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    /**
     * @return int[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
