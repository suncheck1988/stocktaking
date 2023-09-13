<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\CategoryRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Category\Dto\Category\CategoryResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/category/{id}',
        description: 'Получение категории',
        security: [['bearerAuth' => '[]']],
        tags: ['category'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о категории',
                content: new OA\JsonContent(ref: CategoryResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_CATEGORIES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $category =  $this->categoryRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(CategoryResponse::fromModel($category)->jsonSerialize());
    }
}
