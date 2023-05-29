<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Counterparty;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Client\Repository\CounterpartyRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Counterparty\Dto\Counterparty\CounterpartyResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/counterparty/{id}',
        description: 'Получение контрагента',
        security: [['bearerAuth' => '[]']],
        tags: ['counterparty'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о контрагенте',
                content: new OA\JsonContent(ref: CounterpartyResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly CounterpartyRepository $counterpartyRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_COUNTERPARTIES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $counterparty =  $this->counterpartyRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(CounterpartyResponse::fromModel($counterparty)->jsonSerialize());
    }
}
