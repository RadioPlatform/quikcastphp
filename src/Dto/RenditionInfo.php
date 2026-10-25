<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\HlsState;

final readonly class RenditionInfo extends Dto
{
    /**
     * @param  array<string, mixed>  $audioProperties
     */
    public function __construct(
        public string $name,
        public int $generation,
        public HlsState $state,
        public int $bandwidth,
        public string $codecs,
        public int $targetDuration,
        public int $playlistRevision,
        public ?int $mediaSequence,
        public int $advertisedSegmentCount,
        public int $retainedSegmentCount,
        public string $format,
        public string $container,
        public string $codec,
        public string $contentType,
        public ?string $initializationObject,
        public int $initializationObjectCount,
        public int $mediaObjectCount,
        public array $audioProperties,
        public int $retainedBytes,
        public int $allocationReservedBytes,
        public int $allocationBudget,
        public ?int $lastSegmentIngestAt,
        public ?int $lastPlaylistPublicationAt,
        public ?string $lastIngestSince = null,
        public ?string $lastPublicationSince = null,
        public int $playlistVersion = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $audioProperties = self::arr($data, 'audio_properties');

        return new self(
            self::str($data, 'name'),
            self::int($data, 'generation'),
            HlsState::tryFrom(self::str($data, 'state')) ?? HlsState::Active,
            self::int($data, 'bandwidth'),
            self::str($data, 'codecs'),
            self::int($data, 'target_duration'),
            self::int($data, 'playlist_revision'),
            self::nullableInt($data, 'media_sequence'),
            self::int($data, 'advertised_segment_count'),
            self::int($data, 'retained_segment_count'),
            self::str($data, 'format'),
            self::str($data, 'container'),
            self::str($data, 'codec'),
            self::str($data, 'content_type'),
            self::nullableStr($data, 'initialization_object'),
            self::int($data, 'initialization_object_count'),
            self::int($data, 'media_object_count'),
            $audioProperties,
            self::int($data, 'retained_bytes'),
            self::int($data, 'allocation_reserved_bytes'),
            self::int($data, 'allocation_budget'),
            self::nullableInt($data, 'last_segment_ingest_at'),
            self::nullableInt($data, 'last_playlist_publication_at'),
            self::nullableStr($data, 'last_ingest_since'),
            self::nullableStr($data, 'last_publication_since'),
            self::int($data, 'playlist_version'),
        );
    }
}
