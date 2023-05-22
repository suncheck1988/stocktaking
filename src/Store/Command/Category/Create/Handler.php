<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Create;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Model\Category\Category;
use App\Store\Repository\CategoryRepository;
use App\Store\Specification\Category\UniqueCategoryNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly CategoryRepository $categoryRepository,
        private readonly UniqueCategoryNameSpecification $uniqueCategoryNameSpecification,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $parentCategory = null;

        $parentId = $command->getParentId();
        if ($parentId !== null) {
            $parentCategory = $this->categoryRepository->get(new Uuid($parentId), $client);
        }

        $category = new Category(
            Uuid::generate(),
            $client,
            $parentCategory,
            $command->getName(),
            new DateTimeImmutable(),
            $this->uniqueCategoryNameSpecification
        );

        $this->categoryRepository->add($category);

        $this->flusher->flush();
    }
}
