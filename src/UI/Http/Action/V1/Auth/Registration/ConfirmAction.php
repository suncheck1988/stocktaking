<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Auth\Registration;

use App\Auth\Command\Registration\Common\Confirm\Command;
use App\UI\Http\Action\AbstractAction;
use App\UI\Http\ParamsExtractor;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Post(
        path: '/v1/auth/registration/confirm',
        description: 'Подтверждение электронной почты пользователя после запроса на регистрацию',
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
                description: 'Успешное подтверждении электронной почты',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class ConfirmAction extends AbstractAction
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
