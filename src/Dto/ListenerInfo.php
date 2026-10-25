<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\ListenerState;

final readonly class ListenerInfo extends Dto
{
    public function __construct(
        public string $id,
        public string $mount,
        public ?string $requestedMount,
        public ?string $servingMount,
        public int $generation,
        public int $connectedAt,
        public int $durationMs,
        public ConnectionInfo $connection,
        public int $bytesSent,
        public bool $icyMetadata,
        public ListenerState $state,
        public ?string $connectedSince = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::str($data, 'id'),
            mount: self::str($data, 'mount'),
            requestedMount: self::nullableStr($data, 'requested_mount'),
            servingMount: self::nullableStr($data, 'serving_mount'),
            generation: self::int($data, 'generation'),
            connectedAt: self::int($data, 'connected_at'),
            durationMs: self::int($data, 'duration_ms'),
            connection: ConnectionInfo::fromArray(self::arr($data, 'connection')),
            bytesSent: self::int($data, 'bytes_sent'),
            icyMetadata: self::bool($data, 'icy_metadata'),
            state: ListenerState::tryFrom(self::str($data, 'state')) ?? ListenerState::Connected,
            connectedSince: self::nullableStr($data, 'connected_since'),
        );
    }
}
