<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\FixedAsset;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Dto\FixedAssetSearchDto;
use App\Store\Model\FixedAsset\FixedAsset;
use App\Store\Repository\FixedAssetRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\FixedAsset\Dto\FixedAsset\FixedAssetResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/fixed-asset',
        description: 'Получение списка основных средств',
        security: [['bearerAuth' => '[]']],
        tags: ['fixed-asset'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'categoryId',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'warehouseId',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'serialNumber',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'int')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'int')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список основных средств клиента',
                content: new OA\JsonContent(ref: FixedAssetResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly FixedAssetRepository $fixedAssetRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_FIXED_ASSETS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new FixedAssetSearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->categoryId = $paramsExtractor->getStringOrNull('categoryId');
        $searchDto->warehouseId = $paramsExtractor->getStringOrNull('warehouseId');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->serialNumber = $paramsExtractor->getStringOrNull('serialNumber');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');

        $pagination = new PaginationDto(
            $paramsExtractor->getIntOrNull('page') ?? 1,
            $this->fixedAssetRepository->count($this->authContext->getClient(), $searchDto)
        );

        $items = $this->fixedAssetRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (FixedAsset $fixedAsset) => FixedAssetResponse::fromModel($fixedAsset)->jsonSerialize(),
            $items
        );

        return $this->asJson(
            [
                'data' => $data,
                'X-Page-Count' => $pagination->getTotalPages(),
            ]
        );
    }
}
