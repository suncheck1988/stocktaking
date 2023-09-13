<?php

/** @noinspection PhpMissingParentConstructorInspection */

declare(strict_types=1);

namespace App\UI\Http\Action;

use OpenApi\Attributes as OA;
use OpenApi\Generator;
use OpenApi\Util;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[
    OA\Info(
        version: '3',
        title: 'Tanuki waiter API'
    ),
    OA\Get(
        path: '/',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success response'
            ),
        ]
    ),
    OA\SecurityScheme(
        securityScheme: 'bearerAuth',
        type: 'http',
        name: 'bearerAuth',
        in: 'header',
        bearerFormat: 'JWT',
        scheme: 'bearer'
    ),
]
class SwaggerAction extends AbstractAction
{
    public function __construct(
        private readonly Twig $twig
    ) {
    }

    /**
     * @psalm-suppress PossiblyNullArgument
     * @psalm-suppress DeprecatedFunction
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (\array_key_exists('json', $request->getQueryParams())) {
            $openapi = Generator::scan(Util::finder(__DIR__ . '/../Action'));
            $json = $openapi?->toJson();
            return $this->asJsonString($json);
        }

        try {
            return $this->twig->render($this->response, 'swagger.html.twig');
        } catch (LoaderError|RuntimeError|SyntaxError) {
            return $this->asEmpty();
        }
    }
}
