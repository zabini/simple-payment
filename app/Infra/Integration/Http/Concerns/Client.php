<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Concerns;

use Exception;
use GuzzleHttp\Client as HttpClient;
use stdClass;

abstract class Client
{
    /**
     * @var HttpClient
     */
    private $http;

    public function __construct(
        string $baseUri,
        private ?array $extras = null
    ) {
        $options = [
            'base_uri' => $baseUri,
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

    abstract protected function bindAuth(?array $options): array;

    abstract protected function responseHandler(): string;

    private function bindLogHandler(array $options): array
    {
        return $options;
    }
}
