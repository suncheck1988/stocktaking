<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Counterparty;

use App\Application\Dto\PaginationDto;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Client\Dto\CounterpartySearchDto;
use App\Client\Model\Counterparty\Counterparty;
use App\Client\Repository\CounterpartyRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Counterparty\Dto\Counterparty\CounterpartyResponse;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/counterparty',
        description: 'Получение списка контрагентов',
        security: [['bearerAuth' => '[]']],
        tags: ['counterparty'],
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
                name: 'email',
                description: 'email',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                description: 'Status',
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
                description: 'Список контрагентов клиента',
                content: new OA\JsonContent(ref: CounterpartyResponse::class)
            ),
        ]
    )
]
class IndexAction extends AbstractAction
{
    public function __construct(
        private readonly CounterpartyRepository $counterpartyRepository
    ) {
    }

    /**
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_COUNTERPARTIES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $searchDto = new CounterpartySearchDto();

        $paramsExtractor = new ParamsExtractor($request->getQueryParams());

        $searchDto->id = $paramsExtractor->getStringOrNull('id');
        $searchDto->name = $paramsExtractor->getStringOrNull('name');
        $searchDto->email = $paramsExtractor->getStringOrNull('email');
        $searchDto->status = $paramsExtractor->getIntOrNull('status');

        $pagination = new PaginationDto(
            $paramsExtractor->getIntOrNull('page') ?? 1,
            $this->counterpartyRepository->count($this->authContext->getClient(), $searchDto)
        );
        $items = $this->counterpartyRepository->fetchAll($this->authContext->getClient(), $searchDto, $pagination);

        $data = array_map(
            static fn (Counterparty $counterparty) => CounterpartyResponse::fromModel($counterparty)->jsonSerialize(),
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
