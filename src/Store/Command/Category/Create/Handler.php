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

        $date = new DateTimeImmutable();

        $category = new Category(
            Uuid::generate(),
            $client,
            null,
            $command->getName(),
            $date,
            $this->uniqueCategoryNameSpecification
        );

        $this->categoryRepository->add($category);

        foreach ($command->getChildren() as $child) {
            $category->addChild(
                Uuid::generate(),
                $child->getName(),
                $date,
                $this->uniqueCategoryNameSpecification
            );
        }

        $this->flusher->flush();
    }
}
