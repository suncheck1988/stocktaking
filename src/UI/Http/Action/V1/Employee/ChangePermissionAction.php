<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Employee;

use App\Client\Command\Employee\ChangePermission\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/employee/{id}/change-permission',
        description: 'Обновление прав сотрудника',
        security: [['bearerAuth' => '[]']],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['permissions'],
                properties: [
                    new OA\Property(property: 'permissions', type: 'array'),
                ],
            )
        ),
        tags: ['employee'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление прав сотрудника',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class ChangePermissionAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotClientAndCheckEmployee($this->resolveArg('id'));

        $command = $this->deserialize($request);
        $this->validator->validate($command);

        $this->bus->handle($command);

        return $this->asEmpty();
    }

    private function deserialize(ServerRequestInterface $request): Command
    {
        $paramsExtractor = ParamsExtractor::fromRequest($request);

        /** @var array<array-key, int> $permissions */
        $permissions = $paramsExtractor->getSimpleArray('permissions');

        return new Command(
            $this->resolveArg('id'),
            $permissions
        );
    }
}
