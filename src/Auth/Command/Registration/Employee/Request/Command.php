<?php

declare(strict_types=1);

namespace App\Auth\Command\Registration\Employee\Request;

use Symfony\Component\Validator\Constraints\NotBlank;

class Command
{
    /**
     * @param int[] $permissions
     */
    public function __construct(
        #[NotBlank]
        private readonly string $clientUserId,
        #[NotBlank]
        private readonly string $name,
        #[NotBlank]
        private readonly string $email,
        #[NotBlank]
        private readonly string $password,
        private readonly bool $isFinanciallyResponsiblePerson,
        #[NotBlank]
        private readonly array $permissions
    ) {
    }

    public function getClientUserId(): string
    {
        return $this->clientUserId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
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
