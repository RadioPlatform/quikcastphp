<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class Ban extends Dto
{
    public function __construct(
        public string $ip,
        public int $expiresInSeconds,
        public ?string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'ip'),
            self::int($data, 'expires_in_seconds'),
            self::nullableStr($data, 'reason'),
        );
    }
}
