<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Response;

use App\Core\Domain\Exceptions\InvalidOperation;
use App\Infra\Integration\Http\Concerns\ResponseHandler;
use Exception;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class AuthorizerResponse extends ResponseHandler
{
    protected static function parseException(Exception $exception): Exception
    {
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $payload = (string) $exception->getResponse()->getBody();
            $decoded = json_decode($payload);

            if ($decoded instanceof stdClass) {
                $authorization = $decoded->data->authorization ?? null;
                if ($authorization === false) {
                    return InvalidOperation::fromExternalReason('Some Dummy Reason');
                }

                if (isset($decoded->message) && is_string($decoded->message)) {
                    return InvalidOperation::fromExternalReason($decoded->message);
                }
            }
        }

        return InvalidOperation::unmappedReason(sprintf(
            'Failed to authorize: Message: %s | Class: %s',
            $exception->getMessage(),
            get_class($exception)
        ));
    }
}
