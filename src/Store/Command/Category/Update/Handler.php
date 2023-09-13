<?php

declare(strict_types=1);

namespace App\Store\Command\Category\Update;

use App\Application\Exception\DomainException;
use App\Application\ValueObject\Uuid;
use App\Auth\Service\AuthContext;
use App\Data\Flusher;
use App\Data\TransactionManager;
use App\Store\Repository\CategoryRepository;
use App\Store\Specification\Category\UniqueCategoryNameSpecification;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Throwable;

class Handler
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly CategoryRepository $categoryRepository,
        private readonly UniqueCategoryNameSpecification $uniqueCategoryNameSpecification,
        private readonly TransactionManager $transactionManager,
        private readonly Flusher $flusher
    ) {
    }

    /**
     * @throws AssertionFailedException|NonUniqueResultException
     * @throws NoResultException
     * @throws Throwable
     */
    public function handle(Command $command): void
    {
        $client = $this->authContext->getClient();

        $category = $this->categoryRepository->get(new Uuid($command->getId()), $client);

        $parent = $category->getParent();

        if ($parent === null) {
            if (empty($command->getChildren()) && empty($command->getNewChildren())) {
                throw new DomainException('Для корневой категории обязательно должны быть указаны дочерние категории');
            }

            $this->transactionManager->beginTransaction();
            try {
                foreach ($command->getChildren() as $child) {
                    $childCategory = $this->categoryRepository->get(new Uuid($child->getId()), $client);
                    if ($childCategory->getParent() === null) {
                        throw new DomainException(sprintf('У дочерней категории %s не указана корневая категория', $childCategory->getId()->getValue()));
                    }
                    if ($childCategory->getParent()?->getId()->getValue() !== $category->getId()->getValue()) {
                        throw new DomainException(
                            sprintf(
                                'У дочерней категории %s не совпадает корневая категория, указано %s',
                                $childCategory->getId()->getValue(),
                                $category->getId()->getValue()
                            )
                        );
                    }

                    if ($child->isRemove()) {
                        $this->categoryRepository->remove($childCategory);
                    }
                }

                foreach ($command->getNewChildren() as $newChild) {
                    $category->addChild(
                        Uuid::generate(),
                        $newChild->getName(),
                        new DateTimeImmutable(),
                        $this->uniqueCategoryNameSpecification
                    );
                }

                $this->flusher->flush();
                $this->transactionManager->commit();
            } catch (Throwable $e) {
                $this->transactionManager->rollback();
                throw $e;
            }
        } else {
            $parentId = $command->getParentId();
            if ($parentId === null) {
                throw new DomainException('Для дочерней категории обязательно должна быть указана корневая категория');
            }

            //$parentCategory = $this->categoryRepository->get(new Uuid($parentId), $client);
            //$category->changeParent($parentCategory);
        }

        $category->update($command->getName());

        $this->flusher->flush();
    }
}
