<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Warehouse;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Dto\WarehouseSearchDto;
use App\Store\Model\Warehouse\Warehouse;
use App\Store\Repository\WarehouseRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Warehouse\Dto\Warehouse\WarehouseResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/warehouse',
        description: 'Получение списка складов',
        security: [['bearerAuth' => '[]']],
        tags: ['warehouse'],
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
                description: 'Список складов клиента',
                content: new OA\JsonContent(ref: WarehouseResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly WarehouseRepository $warehouseRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_WAREHOUSES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new WarehouseSearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');
        $searchDto->withoutBalanceByPositionId = $paramsExtractor->getStringOrNull('withoutBalanceByPositionId');

        $isWithoutPagination = $paramsExtractor->getBoolOrNull('withoutPagination') ?? false;

        $pagination = null;
        if (!$isWithoutPagination) {
            $pagination = new PaginationDto(
                $paramsExtractor->getIntOrNull('page') ?? 1,
                $this->warehouseRepository->count($this->authContext->getClient(), $searchDto)
            );
        }

        $items = $this->warehouseRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (Warehouse $warehouse) => WarehouseResponse::fromModel($warehouse)->jsonSerialize(),
            $items
        );

        return $this->asJson(
            [
                'data' => $data,
                'X-Page-Count' => $pagination !== null ? $pagination->getTotalPages() : 0,
            ]
        );
    }
}
