<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Auth\Registration\Client;

use App\Auth\Command\Registration\Client\Request\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/registration/client/request',
        description: 'Регистрация клиента',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                ],
            )
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'При успешном запросе регистрации клиента, будет отправлено письмо с ссылкой для подтверждения на указанный email',
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
        $command = $this->deserialize($request);
        $this->validator->validate($command);

        $this->bus->handle($command);

        return $this->asEmpty();
    }

    private function deserialize(ServerRequestInterface $request): Command
    {
        $paramsExtractor = ParamsExtractor::fromRequest($request);

        return new Command(
            trim($paramsExtractor->getString('name')),
            trim($paramsExtractor->getString('email')),
            trim($paramsExtractor->getString('password'))
        );
    }
}
