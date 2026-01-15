<?php

declare(strict_types=1);

namespace App\Infra\Integration\Http\Concerns;

use App\Infra\Exception\JsonException;
use Exception;
use stdClass;

use const JSON_ERROR_NONE;

abstract class ResponseHandler
{
    public static function success(string $payload): ?stdClass
    {
        if (empty($payload)) {
            return null;
        }
        return self::toJson($payload);
    }

    /**
     * @throws Exception
     */
    public static function failure(Exception $originalException)
    {
        throw static::parseException($originalException);
    }

    abstract protected static function parseException(Exception $exception): Exception;

    private static function toJson(string $json): stdClass
    {
        $result = json_decode($json);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw JsonException::invalid(json_last_error_msg());
        }

        if (is_array($result)) {
            return (object) $result;
        }

        return $result;
    }
}
