<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Category;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Dto\CategorySearchDto;
use App\Store\Model\Category\Category;
use App\Store\Repository\CategoryRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Category\Dto\Category\CategoryResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/category',
        description: 'Получение списка категорий',
        security: [['bearerAuth' => '[]']],
        tags: ['category'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'onlyRoot',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'bool')
            ),
            new OA\Parameter(
                name: 'status',
                description: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'int')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'int')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список категорий клиента',
                content: new OA\JsonContent(ref: CategoryResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_CATEGORIES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new CategorySearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');
        $searchDto->onlyRoot = $paramsExtractor->getBool('onlyRoot');

        $isWithoutPagination = $paramsExtractor->getBoolOrNull('withoutPagination') ?? false;

        $pagination = null;
        if (!$isWithoutPagination) {
            $pagination = new PaginationDto(
                $paramsExtractor->getIntOrNull('page') ?? 1,
                $this->categoryRepository->count($this->authContext->getClient(), $searchDto)
            );
        }

        $items = $this->categoryRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $result = [
            'data' => array_map(
                static fn (Category $category) => CategoryResponse::fromModel($category)->jsonSerialize(),
                $items
            ),
        ];

        if ($pagination !== null) {
            $result['X-Page-Count'] = $pagination->getTotalPages();
        }

        return $this->asJson($result);
    }
}
