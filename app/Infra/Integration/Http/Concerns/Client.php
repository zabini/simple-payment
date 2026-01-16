<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Concerns;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use stdClass;

abstract class Client
{
    private HttpClient $http;
    private LoggerInterface $logger;

    public function __construct(
        LoggerFactory $loggerFactory,
        ?string $baseUri = null,
        ?array $extras = null,
    ) {
        $this->logger = $loggerFactory->get('integration', 'integration');

        $options = [
            'base_uri' => $baseUri ?? $this->baseUri(),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ];

        if (! is_null($extras)) {
            $options = array_merge($options, $extras);
        }

        $options = $this->bindLogHandler($options);

        $this->http = new HttpClient($options);
    }

    public function request(string $method, string $uri, array $options = []): ?stdClass
    {
        $responseHandler = $this->responseHandler();

        try {
            $response = $this->http->request(
                $method,
                $uri,
                $this->bindAuth($options)
            );
            return $responseHandler::success(
                (string) $response->getBody()
            );
        } catch (Exception $exception) {
            $responseHandler::failure($exception);
            return null;
        }
    }

    abstract protected function baseUri(): string;

    abstract protected function bindAuth(?array $options): array;

    abstract protected function responseHandler(): string;

    private function bindLogHandler(array $options): array
    {
        $handlerStack = $options['handler'] ?? HandlerStack::create();

        if (! $handlerStack instanceof HandlerStack) {
            $handlerStack = HandlerStack::create($handlerStack);
        }

        $handlerStack->push(Middleware::log(
            $this->logger,
            new MessageFormatter(MessageFormatter::SHORT)
        ));

        $options['handler'] = $handlerStack;

        return $options;
    }
}
