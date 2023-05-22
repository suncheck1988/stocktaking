<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Auth\Registration;

use App\Auth\Command\Registration\Common\RecreateEmailConfirm\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/registration/recreate-email-confirm',
        description: 'Создание новой ссылки для подтверждения электронной почты пользователя после запроса на регистрацию',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['token'],
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                ],
            )
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'При успешном создании новой ссылки для подтверждения регистрации пользователя, будет отправлено письмо с ссылкой для подтверждения на указанный email',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class RecreateEmailConfirmAction extends AbstractAction
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
            $paramsExtractor->getString('token')
        );
    }
}
