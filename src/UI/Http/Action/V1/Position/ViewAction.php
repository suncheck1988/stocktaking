<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Position;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Store\Repository\PositionRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Position\Dto\Position\PositionResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/position/{id}',
        description: 'Получение позиции',
        security: [['bearerAuth' => '[]']],
        tags: ['position'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о позиции',
                content: new OA\JsonContent(ref: PositionResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_POSITIONS],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $warehouse =  $this->positionRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(PositionResponse::fromModel($warehouse)->jsonSerialize());
    }
}
