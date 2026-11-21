<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class Revocation extends Dto
{
    public function __construct(
        public string $signature,
        public int $expiresInSeconds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'signature'),
            self::int($data, 'expires_in_seconds'),
        );
    }
}
