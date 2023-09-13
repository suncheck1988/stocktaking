<?php

declare(strict_types=1);

namespace App\Store\Specification\Category;

use App\Store\Model\Category\Category;
use App\Store\Repository\CategoryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UniqueCategoryNameSpecification
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isSatisfiedBy(Category $category): bool
    {
        return !$this->categoryRepository->existByName($category);
    }
}
