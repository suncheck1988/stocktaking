<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Active;

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
        $category = $this->categoryRepository->get(new Uuid($command->getId()), $this->authContext->getClient());

        $category->active();

        $this->flusher->flush();
    }
}
