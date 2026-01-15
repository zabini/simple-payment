<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Response;

use App\Core\Domain\Exceptions\NotificationException;
use App\Infra\Exception\GatewayTimeoutException;
use App\Infra\Integration\Http\Concerns\ResponseHandler;
use Exception;
use GuzzleHttp\Exception\RequestException;

class NotifierResponse extends ResponseHandler
{
    protected static function parseException(Exception $exception): Exception
    {
        if ($exception instanceof RequestException) {
            return new GatewayTimeoutException('Gateway Timeout');
        }

        return NotificationException::unmappedReason(sprintf(
            'Failed to notify payee: Message: %s | Class: %s',
            $exception->getMessage(),
            get_class($exception)
        ));
    }
}
