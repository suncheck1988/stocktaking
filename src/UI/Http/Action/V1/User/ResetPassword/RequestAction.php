<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\User\ResetPassword;

use App\Auth\Command\User\ResetPassword\Request\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/user/reset-password/request',
        description: 'Запрос сброса пароля пользователя',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                ],
            )
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'При успешном запросе сброса пароля будет отправлено письмо на email для подтверждения'
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
            trim($paramsExtractor->getString('email'))
        );
    }
}
