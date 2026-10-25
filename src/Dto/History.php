<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class History extends Dto
{
    /**
     * @param  list<HistoryMinute>  $minutes
     * @param  list<HistoryHour>  $hours
     */
    public function __construct(
        public ?string $mount,
        public int $windowMinutes,
        public int $retentionMinutes,
        public int $peakListeners24h,
        public int $listenerStarts24h,
        public int $uniqueListenersEstimate24h,
        public array $minutes,
        public array $hours,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::nullableStr($data, 'mount'),
            self::int($data, 'window_minutes'),
            self::int($data, 'retention_minutes'),
            self::int($data, 'peak_listeners_24h'),
            self::int($data, 'listener_starts_24h'),
            self::int($data, 'unique_listeners_estimate_24h'),
            array_map(HistoryMinute::fromArray(...), self::mapList($data['minutes'] ?? [])),
            array_map(HistoryHour::fromArray(...), self::mapList($data['hours'] ?? [])),
        );
    }
}
