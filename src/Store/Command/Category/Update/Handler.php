<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Update;

use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Store\Repository\CategoryRepository;
use Assert\AssertionFailedException;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly CategoryRepository $categoryRepository,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $category = $this->categoryRepository->get(new Uuid($command->getId()), $client);

        $parentCategory = null;

        $parentId = $command->getParentId();
        if ($parentId !== null) {
            $parentCategory = $this->categoryRepository->get(new Uuid($parentId), $client);
        }

        $category->update($command->getName());
        $category->changeParent($parentCategory);

        $this->flusher->flush();
    }
}
