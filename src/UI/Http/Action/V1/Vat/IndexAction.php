<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Vat;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Dto\VatSearchDto;
use App\Store\Model\Vat\Vat;
use App\Store\Repository\VatRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Vat\Dto\Vat\VatResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/vat',
        description: 'Получение списка ставок НДС',
        security: [['bearerAuth' => '[]']],
        tags: ['vat'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'isDefault',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'bool')
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
                description: 'Список ставок НДС клиента',
                content: new OA\JsonContent(ref: VatResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly VatRepository $vatRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_VATS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new VatSearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->isDefault = $paramsExtractor->getBoolOrNull('isDefault');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');

        $isWithoutPagination = $paramsExtractor->getBoolOrNull('withoutPagination') ?? false;

        $pagination = null;
        if (!$isWithoutPagination) {
            $pagination = new PaginationDto(
                $paramsExtractor->getIntOrNull('page') ?? 1,
                $this->vatRepository->count($this->authContext->getClient(), $searchDto)
            );
        }

        $items = $this->vatRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (Vat $vat) => VatResponse::fromModel($vat)->jsonSerialize(),
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
