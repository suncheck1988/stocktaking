<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Employee;

use App\Application\ValueObject\Uuid;
use App\Auth\Model\User\Permission;
use App\Auth\Model\User\Role;
use App\Client\Repository\EmployeeRepository;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\Employee\Dto\Employee\EmployeeResponse;
use Assert\AssertionFailedException;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/employee/{id}',
        description: 'Получение сотрудника',
        security: [['bearerAuth' => '[]']],
        tags: ['employee'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о сотруднике',
                content: new OA\JsonContent(ref: EmployeeResponse::class)
            ),
        ]
    )
]
class ViewAction extends AbstractAction
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository
    ) {
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotHasPermissionsByRoles(
            [Permission::SECTION_EMPLOYEES],
            [Role::CLIENT, Role::CLIENT_EMPLOYEE]
        );

        $employee =  $this->employeeRepository->get(
            new Uuid($this->resolveArg('id')),
            $this->authContext->getClient()
        );

        return $this->asJson(EmployeeResponse::fromModel($employee)->jsonSerialize());
    }
}
