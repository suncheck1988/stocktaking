<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\DeliveryType;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Order\Dto\DeliveryTypeSearchDto;
use App\Order\Model\DeliveryType\DeliveryType;
use App\Order\Repository\DeliveryTypeRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\DeliveryType\Dto\DeliveryType\DeliveryTypeResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/delivery-type',
        description: 'Получение списка типов доставки',
        security: [['bearerAuth' => '[]']],
        tags: ['delivery-type'],
        parameters: [
            new OA\Parameter(
                name: 'id',
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
                description: 'Список типов доставки клиента',
                content: new OA\JsonContent(ref: DeliveryTypeResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly DeliveryTypeRepository $deliveryTypeRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_DELIVERY_TYPES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new DeliveryTypeSearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');

        $isWithoutPagination = $paramsExtractor->getBoolOrNull('withoutPagination') ?? false;

        $pagination = null;
        if (!$isWithoutPagination) {
            $pagination = new PaginationDto(
                $paramsExtractor->getIntOrNull('page') ?? 1,
                $this->deliveryTypeRepository->count($this->authContext->getClient(), $searchDto)
            );
        }

        $items = $this->deliveryTypeRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (DeliveryType $deliveryType) => DeliveryTypeResponse::fromModel($deliveryType)->jsonSerialize(),
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
