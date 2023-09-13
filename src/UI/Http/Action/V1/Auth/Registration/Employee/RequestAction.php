<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Auth\Registration\Employee;

use App\Auth\Command\Registration\Employee\Request\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/registration/employee/request',
        description: 'Регистрация сотрудника клиента',
        security: [['bearerAuth' => '[]']],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'isFinanciallyResponsiblePerson'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'isFinanciallyResponsiblePerson', type: 'boolean'),
                    new OA\Property(property: 'permissions', type: 'array'),
                ],
            )
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'При успешном запросе регистрации сотрудника клиента, будет отправлено письмо с ссылкой для подтверждения на указанный email',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class RequestAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotClient();

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
            $this->getCurrentUser()->getId()->getValue(),
            trim($paramsExtractor->getString('name')),
            trim($paramsExtractor->getString('email')),
            trim($paramsExtractor->getString('password')),
            $paramsExtractor->getBool('isFinanciallyResponsiblePerson'),
            $permissions
        );
    }
}
