<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\RelayState;

final readonly class RelaySnapshot extends Dto
{
    public function __construct(
        public string $mount,
        public int $upstreamCount,
        public ?int $currentUpstreamIndex,
        public ?string $currentUpstream,
        public bool $upstreamUsable,
        public int $attemptCycle,
        public int $consecutiveFailures,
        public ?string $lastFailure,
        public RelayState $state,
        public bool $desiredRunning,
        public int $revision,
        public int $attempt,
        public ?int $generation,
        public ?int $connectedAt,
        public ?int $connectionAgeMs,
        public ?int $nextRetryAt,
        public ?int $retryInMs,
        public ?int $retryDelayMs,
        public ?string $stoppedReason,
        public ?string $lastError,
        public ?int $lastUpstreamStatus,
        public int $attemptsTotal,
        public int $connectionsTotal,
        public int $reconnectsTotal,
        public int $responseBodyBytesTotal,
        public int $audioBytesTotal,
        public int $metadataRejectionsTotal,
        public int $allocationReservationBytes,
        public ?string $connectedSince = null,
        public ?string $backoffUntil = null,
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
            self::str($data, 'mount'),
            self::int($data, 'upstream_count'),
            self::nullableInt($data, 'current_upstream_index'),
            self::nullableStr($data, 'current_upstream'),
            self::bool($data, 'upstream_usable'),
            self::int($data, 'attempt_cycle'),
            self::int($data, 'consecutive_failures'),
            self::nullableStr($data, 'last_failure'),
            RelayState::tryFrom(self::str($data, 'state')) ?? RelayState::Stopped,
            self::bool($data, 'desired_running'),
            self::int($data, 'revision'),
            self::int($data, 'attempt'),
            self::nullableInt($data, 'generation'),
            self::nullableInt($data, 'connected_at'),
            self::nullableInt($data, 'connection_age_ms'),
            self::nullableInt($data, 'next_retry_at'),
            self::nullableInt($data, 'retry_in_ms'),
            self::nullableInt($data, 'retry_delay_ms'),
            self::nullableStr($data, 'stopped_reason'),
            self::nullableStr($data, 'last_error'),
            self::nullableInt($data, 'last_upstream_status'),
            self::int($data, 'attempts_total'),
            self::int($data, 'connections_total'),
            self::int($data, 'reconnects_total'),
            self::int($data, 'response_body_bytes_total'),
            self::int($data, 'audio_bytes_total'),
            self::int($data, 'metadata_rejections_total'),
            self::int($data, 'allocation_reservation_bytes'),
            self::nullableStr($data, 'connected_since'),
            self::nullableStr($data, 'backoff_until'),
            self::int($data, 'failovers_total'),
            self::int($data, 'upstream_failures_total'),
            self::int($data, 'cycles_exhausted_total'),
        );
    }
}
