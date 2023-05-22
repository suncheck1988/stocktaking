<?php

declare(strict_types=1);

namespace Test\Functional;

use App\UI\Http\ErrorHandler;
use Exception;
use JsonException;
use League\Uri\HttpFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Test\TestKernel;

class Client extends AbstractBrowser
{
    /**
     * @return Response
     */
    public function getResponse(): object
    {
        return parent::getResponse();
    }

    /**
     * @throws Exception
     */
    public function doRequest(object $request): Response
    {
        AppFactory::setContainer(TestKernel::getContainer());
        $app = AppFactory::create();

        $routes = TestKernel::getRoutes();
        $routes($app);

        $app->addRoutingMiddleware();

        $callableResolver = TestKernel::getContainer()->get(CallableResolverInterface::class);
        $responseFactory = TestKernel::getContainer()->get(ResponseFactoryInterface::class);
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{display_details:bool} $config
         */
        $config = TestKernel::getContainer()->get('config')['errors'];

        $middleware = new ErrorMiddleware(
            $callableResolver,
            $responseFactory,
            $config['display_details'],
            true,
            true
        );

        $logger = TestKernel::getContainer()->get(LoggerInterface::class);

        $errorHandler = new ErrorHandler($callableResolver, $responseFactory, $logger);

        $middleware->setDefaultErrorHandler($errorHandler);

        /** @var Request $request */
        $httpRequest = new \Slim\Psr7\Request(
            $request->getMethod(),
            (new HttpFactory())->createUri($request->getUri()),
            new Headers($request->getParameters()['headers'] ?? []),
            $request->getCookies(),
            $request->getServer(),
            new Stream(fopen(sprintf('data://text/plain,%s', $request->getContent()), 'rb')),
            $request->getFiles(),
        );

        $httpRequest = $httpRequest->withParsedBody($this->extractPostParams($request));

        $httpResponse = $app->handle($httpRequest);

        return new Response((string) $httpResponse->getBody(), $httpResponse->getStatusCode(), $httpResponse->getHeaders());
    }

    /**
     * @throws JsonException
     */
    private function extractPostParams(Request $testCaseRequest): ?array
    {
        /** @var string|null $contentType */
        $contentType = $testCaseRequest->getParameters()['headers']['Content-Type'] ?? null;

        if ($contentType === null) {
            return null;
        }

        switch ($contentType) {
            case 'application/x-www-form-urlencoded':
                $content = [];
                parse_str($testCaseRequest->getContent() ?? '', $content);
                break;
            case 'application/json':
                /** @var array $content */
                $content = json_decode($testCaseRequest->getContent() ?? '', true, 512, JSON_THROW_ON_ERROR);
                break;
            default:
                $content = null;
        }

        return $content;
    }
}