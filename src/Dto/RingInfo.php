<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class RingInfo extends Dto
{
    public function __construct(
        public int $chunks,
        public int $retainedBytes,
        public ?int $oldestSequence,
        public ?int $latestSequence,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'chunks'),
            self::int($data, 'retained_bytes'),
            self::nullableInt($data, 'oldest_sequence'),
            self::nullableInt($data, 'latest_sequence'),
        );
    }
}
