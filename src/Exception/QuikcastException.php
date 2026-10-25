<?php

declare(strict_types=1);

namespace Quikcast\Exception;

use Quikcast\Enum\ErrorCode;
use RuntimeException;

class QuikcastException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ErrorCode $errorCode,
        public readonly ?string $allow = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
