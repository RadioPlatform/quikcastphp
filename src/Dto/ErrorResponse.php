<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\ErrorCode;

final readonly class ErrorResponse
{
    public function __construct(
        public ErrorCode $code,
        public string $message,
    ) {}

    public static function fromArray(mixed $data): self
    {
        $root = is_array($data) ? $data : [];
        $error = [];

        foreach ($root as $k => $v) {
            if ($k === 'error' && is_array($v)) {
                foreach ($v as $ek => $ev) {
                    if (is_string($ek)) {
                        $error[$ek] = $ev;
                    }
                }
            }
        }

        $code = isset($error['code']) && is_string($error['code'])
            ? (ErrorCode::tryFrom($error['code']) ?? ErrorCode::Unknown)
            : ErrorCode::Unknown;
        $message = isset($error['message']) && is_string($error['message'])
            ? $error['message']
            : 'Quikcast request failed';

        return new self($code, $message);
    }
}
