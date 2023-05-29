<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Employee;

use App\Client\Command\Employee\Update\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/employee/{id}',
        description: 'Обновление сотрудника',
        security: [['bearerAuth' => '[]']],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name', 'isFinanciallyResponsiblePerson'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'isFinanciallyResponsiblePerson', type: 'bool'),
                ],
            )
        ),
        tags: ['employee'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешное обновление сотрудника',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class UpdateAction extends AbstractAction
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

        $user = new ParamsExtractor($paramsExtractor->getSimpleArray('user'));

        /** @var array<array-key, int> $permissions */
        $permissions = $user->getSimpleArray('permissions');

        return new Command(
            $this->resolveArg('id'),
            trim($user->getString('name')),
            $paramsExtractor->getBool('isFinanciallyResponsiblePerson'),
            $permissions
        );
    }
}
