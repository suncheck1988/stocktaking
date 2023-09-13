<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Position;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Dto\PositionSearchDto;
use App\Store\Model\Position\Position;
use App\Store\Repository\PositionRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Position\Dto\Position\PositionResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/position',
        description: 'Получение списка позиций',
        security: [['bearerAuth' => '[]']],
        tags: ['position'],
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
                description: 'Список позиций клиента',
                content: new OA\JsonContent(ref: PositionResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_POSITIONS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new PositionSearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');

        $pagination = new PaginationDto(
            $paramsExtractor->getIntOrNull('page') ?? 1,
            $this->positionRepository->count($this->authContext->getClient(), $searchDto)
        );

        $items = $this->positionRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (Position $position) => PositionResponse::fromModel($position)->jsonSerialize(),
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
