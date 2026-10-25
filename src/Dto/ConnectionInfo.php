<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class ConnectionInfo extends Dto
{
    public function __construct(
        public string $remoteAddress,
        public string $clientIp,
        public ?string $userAgent,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'remote_address'),
            self::str($data, 'client_ip'),
            self::nullableStr($data, 'user_agent'),
        );
    }
}
