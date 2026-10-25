<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\HlsState;

final readonly class StreamInfo extends Dto
{
    public function __construct(
        public string $name,
        public int $generation,
        public HlsState $state,
        public int $playlistRevision,
        public int $renditionCount,
        public int $retainedBytes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'name'),
            self::int($data, 'generation'),
            HlsState::tryFrom(self::str($data, 'state')) ?? HlsState::Active,
            self::int($data, 'playlist_revision'),
            self::int($data, 'rendition_count'),
            self::int($data, 'retained_bytes'),
        );
    }
}
