<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class HistoryMinute extends Dto
{
    public function __construct(
        public int $startMs,
        public int $peakListeners,
        public int $listenerStarts,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'start_ms'),
            self::int($data, 'peak_listeners'),
            self::int($data, 'listener_starts'),
        );
    }
}
