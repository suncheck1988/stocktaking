<?php

/** @noinspection DoctrineTypeDeprecatedInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category\Dto\Category;

use App\Application\Dto\JsonResponseInterface;
use App\Store\Model\Category\Category;
use Exception;
use OpenApi\Attributes as OA;

#[
    OA\Schema(
        schema: 'CategoryResponse',
        title: 'Category',
        description: 'Category response',
        required: ['id', 'name', 'children', 'status']
    )
]
class CategoryResponse implements JsonResponseInterface
{
    public function __construct(
        #[OA\Property]
        private readonly string $id,
        #[OA\Property]
        private readonly string $name,
        #[OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(
                ref: self::class,
                type: 'object'
            )
        )]
        private readonly array $children,
        #[OA\Property]
        private readonly int $status,
        #[OA\Property(
            property: 'parent',
            ref: self::class,
            type: 'json'
        )]
        private readonly ?array $parent = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'children' => $this->children,
            'status' => $this->status,
            'parent' => $this->parent,
        ];
    }

    /**
     * @param Category $model
     * @throws Exception
     */
    public static function fromModel($model): self
    {
        $children = [];
        foreach ($model->getChildren() as $child) {
            $children[] = [
                'id' => $child->getId()->getValue(),
                'name' => $child->getName(),
                'status' => $child->getStatus()->getValue(),
            ];
        }

        $parentDto = null;
        $parent = $model->getParent();

        if ($parent !== null) {
            $parentDto = [
                'id' => $parent->getId()->getValue(),
                'name' => $parent->getName(),
                'status' => $parent->getStatus()->getValue(),
            ];
        }

        return new self(
            $model->getId()->getValue(),
            $model->getName(),
            $children,
            $model->getStatus()->getValue(),
            $parentDto
        );
    }
}
