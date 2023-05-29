<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\ErrorHandler\LogErrorHandler;
use App\Application\Exception\DomainException;
use App\Application\Exception\NotFoundException;
use App\Application\Exception\ValidationException;
use Assert\InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Interfaces\CallableResolverInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ErrorHandler extends LogErrorHandler
{
    protected CallableResolverInterface $callableResolver;

    protected ResponseFactoryInterface $responseFactory;

    protected LoggerInterface $logger;

    public function __construct(
        private readonly TranslatorInterface $translator,
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    protected function respond(): ResponseInterface
    {
        $exception = $ex = $this->exception;

        switch (true) {
            case $exception instanceof ExtraAttributesException:
                $errors = [];
                /** @var string $attribute */
                foreach ($exception->getExtraAttributes() as $attribute) {
                    $errors[] = [
                        'property' => $attribute,
                        'message' => 'The attribute is not allowed.',
                    ];
                }
                return $this->asJson0(['errors' => $errors], 422);
            case $exception instanceof NotNormalizableValueException:
                $errors = [];

                $message = sprintf(
                    'The type must be one of "%s" ("%s" given).',
                    implode(', ', (array)$exception->getExpectedTypes()),
                    $exception->getCurrentType() ?? 'Unknown type'
                );

                $errors[] = [
                    'attribute' => $exception->getPath(),
                    'message' => $message,
                ];
                return $this->asJson0(['errors' => $errors], 422);
            case $exception instanceof PartialDenormalizationException:
                $errors = [];
                /** @var NotNormalizableValueException $ex */
                foreach ($exception->getErrors() as $ex) {
                    $message = sprintf(
                        'The type must be one of "%s" ("%s" given).',
                        implode(', ', (array)$ex->getExpectedTypes()),
                        $ex->getCurrentType() ?? 'Unknown type'
                    );

                    $errors[] = [
                        'attribute' => $ex->getPath(),
                        'message' => $message,
                    ];
                }
                return $this->asJson0(['errors' => $errors], 422);
            case $exception instanceof ValidationException:
                $errors = [];
                foreach ($exception->getViolations() as $violation) {
                    $errors[] = [
                        'property' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage(),
                    ];
                }
                return $this->asJson0(['errors' => $errors], 422);
            case $exception instanceof NotFoundException:
                $errors = [
                    [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                    ],
                ];
                return $this->asJson0(['errors' => $errors], 404);
            case $exception instanceof InvalidArgumentException:
                $errors = [
                    [
                        'property' => $exception->getPropertyPath(),
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                    ],
                ];
                return $this->asJson0(['errors' => $errors], 400);
            case $exception instanceof DomainException:
            case $exception instanceof \App\Application\Exception\InvalidArgumentException:
                $errors = [
                    [
                        'property' => null,
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                    ],
                ];
                return $this->asJson0(['errors' => $errors], 409);

            case $ex instanceof \InvalidArgumentException:
            case $ex instanceof \DomainException:
                $response = $this->asJson(['errors' => [$this->buildError($ex->getMessage())]]);

                break;
            default:
                $response = parent::respond();
        }

        return $response;
    }

    protected function logError(string $error): void
    {
        if ($this->exception instanceof HttpNotFoundException ||
            $this->exception instanceof HttpMethodNotAllowedException ||
            $this->exception instanceof ExtraAttributesException ||
            $this->exception instanceof NotNormalizableValueException ||
            $this->exception instanceof PartialDenormalizationException ||
            $this->exception instanceof ValidationException ||
            $this->exception instanceof InvalidArgumentException ||
            $this->exception instanceof \App\Application\Exception\InvalidArgumentException ||
            $this->exception instanceof HttpUnauthorizedException
        ) {
            return;
        }

        parent::logError($error);
    }

    private function buildError(string $message): array
    {
        return [
            'message' => $message,
            'property' => null,
        ];
    }

    private function asJson(array $data): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(409);

        $response->getBody()->write(json_encode([
            'status' => 409,
            'errors' => isset($data['errors']) && \is_array($data['errors']) ? $data['errors'] : null,
        ], getenv('APP_ENV') === 'dev' ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : 0));

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function asJson0(array $data, int $status): ResponseInterface
    {
        $json = json_encode($data, getenv('APP_ENV') === 'dev' ? JSON_PRETTY_PRINT : 0);
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write($json);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
