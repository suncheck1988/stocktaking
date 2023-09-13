<?php

declare(strict_types=1);

namespace App\Application\Dto;

class PaginationDto
{
    public function __construct(
        private readonly int $page,
        private readonly int $totalCount,
        private readonly ?int $limit = null
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getLimit(): int
    {
        return $this->limit !== null ? $this->limit : 100;
    }

    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    public function getTotalPages(): int
    {
        return (int)ceil($this->getTotalCount() / $this->getLimit());
    }
}
