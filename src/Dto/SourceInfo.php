<?php

declare(strict_types=1);

namespace Quikcast\Dto;

use Quikcast\Enum\SourceState;

final readonly class SourceInfo extends Dto
{
    public function __construct(
        public int $generation,
        public int $connectedAt,
        public int $ageMs,
        public ConnectionInfo $connection,
        public string $contentType,
        public string $format,
        public ?string $codec,
        public SourceMetadata $sourceMetadata,
        public TrackInfo $track,
        public int $bytesReceived,
        public SourceState $state,
        public ?int $sampleRateHz = null,
        public ?int $channels = null,
        public ?int $bitsPerSample = null,
        public ?string $connectedSince = null,
        public ?int $generationAgeMs = null,
        public ?RingInfo $ring = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            generation: self::int($data, 'generation'),
            connectedAt: self::int($data, 'connected_at'),
            ageMs: self::int($data, 'age_ms'),
            connection: ConnectionInfo::fromArray(self::arr($data, 'connection')),
            contentType: self::str($data, 'content_type'),
            format: self::str($data, 'format'),
            codec: self::nullableStr($data, 'codec'),
            sourceMetadata: SourceMetadata::fromArray(self::arr($data, 'source_metadata')),
            track: TrackInfo::fromArray(self::arr($data, 'track')),
            bytesReceived: self::int($data, 'bytes_received'),
            state: SourceState::tryFrom(self::str($data, 'state')) ?? SourceState::Streaming,
            sampleRateHz: self::nullableInt($data, 'sample_rate_hz'),
            channels: self::nullableInt($data, 'channels'),
            bitsPerSample: self::nullableInt($data, 'bits_per_sample'),
            connectedSince: self::nullableStr($data, 'connected_since'),
            generationAgeMs: self::nullableInt($data, 'generation_age_ms'),
            ring: isset($data['ring']) ? RingInfo::fromArray(self::arr($data, 'ring')) : null,
        );
    }
}
