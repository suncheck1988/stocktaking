<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Auth\Login;

use App\Auth\Command\Login\Request\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/login/request',
        description: 'Запрос авторизации пользователя',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                ],
            )
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'При успешной авторизации возвращается JWT-токен',
                content: new OA\JsonContent(
                    required: ['accessToken'],
                    properties: [
                        new OA\Property(property: 'accessToken', type: 'string'),
                    ],
                )
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

        /** @var string $accessToken */
        $accessToken = $this->bus->handle($command);

        return $this->asJson(['accessToken' => $accessToken]);
    }

    private function deserialize(ServerRequestInterface $request): Command
    {
        $paramsExtractor = ParamsExtractor::fromRequest($request);

        return new Command(
            trim($paramsExtractor->getString('email')),
            trim($paramsExtractor->getString('password'))
        );
    }
}
