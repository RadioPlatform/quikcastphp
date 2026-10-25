<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class ServerLimits extends Dto
{
    public function __construct(
        public int $maxConnections,
        public int $maxListeners,
        public int $maxHandshakes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'max_connections'),
            self::int($data, 'max_listeners'),
            self::int($data, 'max_handshakes'),
        );
    }
}
