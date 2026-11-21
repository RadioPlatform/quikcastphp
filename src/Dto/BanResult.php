<?php

declare(strict_types=1);

namespace Quikcast\Dto;

/** Response to creating a ban. */
final readonly class BanResult extends Dto
{
    public function __construct(
        public string $ip,
        public int $expiresInSeconds,
        public int $kickedListeners,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'ip'),
            self::int($data, 'expires_in_seconds'),
            self::int($data, 'kicked_listeners'),
        );
    }
}
