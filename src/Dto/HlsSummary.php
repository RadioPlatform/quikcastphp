<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class HlsSummary extends Dto
{
    public function __construct(
        public int $streamCount,
        public int $renditionCount,
        public int $retainedBytes,
        public int $allocationReservedBytes,
        public int $allocationBudget,
        public int $maxUploads,
        public int $objectCount = 0,
        public int $remainingAllocationBytes = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'stream_count'),
            self::int($data, 'rendition_count'),
            self::int($data, 'retained_bytes'),
            self::int($data, 'allocation_reserved_bytes'),
            self::int($data, 'allocation_budget'),
            self::int($data, 'max_uploads'),
            self::int($data, 'object_count'),
            self::int($data, 'remaining_allocation_bytes'),
        );
    }
}
