<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\User;

use App\UI\Http\Action\AbstractAction;
use App\UI\Http\Action\V1\User\Dto\Me\Response;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Get(
        path: '/v1/user/me',
        description: 'Получение информации о текущем пользователе',
        security: [['bearerAuth' => '[]']],
        tags: ['user'],
        parameters: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о текущем пользователе',
                content: new OA\JsonContent(ref: '#/components/schemas/MeResponse')
            ),
        ]
    )
]
class MeAction extends AbstractAction
{
    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotAuthenticated();

        return $this->asJson(Response::fromModel($this->getCurrentUser())->jsonSerialize());
    }
}
