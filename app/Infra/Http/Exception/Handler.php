<?php

declare(strict_types=1);

namespace App\Infra\Http\Exception;

use App\Core\Domain\Exceptions\DomainException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler as BaseHandler;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Handler extends BaseHandler
{
    #[Inject]
    private HttpResponse $response;

    #[Inject]
    private StdoutLoggerInterface $logger;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof ValidationException) {
            $this->stopPropagation();

            return $this->response->json([
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed.',
                'errors' => $throwable->validator->errors()->toArray(),
            ])->withStatus(422);
        }

        if ($throwable instanceof DomainException) {
            $this->stopPropagation();

            return $this->response->json([
                'success' => false,
                'code' => $throwable->getErrorCode(),
                'message' => $throwable->getMessage(),
                'errors' => $throwable->getErrors(),
            ])->withStatus($throwable->getStatusCode());
        }

        $this->logger->error((string) $throwable);
        $this->stopPropagation();

        return $this->response->json([
            'success' => false,
            'code' => 'INTERNAL_ERROR',
            'message' => 'Internal server error.',
        ])->withStatus(500);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
