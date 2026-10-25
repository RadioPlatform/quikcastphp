<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class HistoryHour extends Dto
{
    public function __construct(
        public int $startMs,
        public int $uniqueListenersEstimate,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'start_ms'),
            self::int($data, 'unique_listeners_estimate'),
        );
    }
}
