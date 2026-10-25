<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class RelaySummary extends Dto
{
    public function __construct(
        public int $configured,
        public int $connecting,
        public int $connected,
        public int $backoff,
        public int $stopped,
        public int $attemptsTotal,
        public int $connectionsTotal,
        public int $reconnectsTotal,
        public int $failuresTotal,
        public int $responseBodyBytesTotal,
        public int $audioBytesTotal,
        public int $metadataRejectionsTotal,
        public int $allocationReservationBytes,
        public int $failoversTotal = 0,
        public int $upstreamFailuresTotal = 0,
        public int $cyclesExhaustedTotal = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'configured'),
            self::int($data, 'connecting'),
            self::int($data, 'connected'),
            self::int($data, 'backoff'),
            self::int($data, 'stopped'),
            self::int($data, 'attempts_total'),
            self::int($data, 'connections_total'),
            self::int($data, 'reconnects_total'),
            self::int($data, 'failures_total'),
            self::int($data, 'response_body_bytes_total'),
            self::int($data, 'audio_bytes_total'),
            self::int($data, 'metadata_rejections_total'),
            self::int($data, 'allocation_reservation_bytes'),
            self::int($data, 'failovers_total'),
            self::int($data, 'upstream_failures_total'),
            self::int($data, 'cycles_exhausted_total'),
        );
    }
}
