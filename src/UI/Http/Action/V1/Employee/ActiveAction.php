<?php

declare(strict_types=1);

namespace App\UI\Http\Action\V1\Employee;

use App\Client\Command\Employee\Active\Command;
use App\UI\Http\Action\AbstractAction;
use Assert\AssertionFailedException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[
    OA\Patch(
        path: '/v1/employee/{id}/active',
        description: 'Активация сотрудника',
        security: [['bearerAuth' => '[]']],
        tags: ['employee'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Успешная активация сотрудника',
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации или бизнес-логики'
            ),
        ],
    )
]
class ActiveAction extends AbstractAction
{
    /**
     * @throws AssertionFailedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->denyAccessNotClientAndCheckEmployee($this->resolveArg('id'));

        $command = $this->deserialize();
        $this->validator->validate($command);

        $this->bus->handle($command);

        return $this->asEmpty();
    }

    private function deserialize(): Command
    {
        return new Command($this->resolveArg('id'));
    }
}
