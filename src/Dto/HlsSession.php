<?php

declare(strict_types=1);

namespace Quikcast\Dto;

/** One listener session of a directory HLS stream (a client address plus user agent). */
final readonly class HlsSession extends Dto
{
    public function __construct(
        public string $id,
        public string $ip,
        public ?string $userAgent,
        public ?string $country,
        public int $firstSeen,
        public int $lastSeen,
        public int $durationSeconds,
        public int $playlistRequests,
        public int $segmentRequests,
        public int $bytesServed,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'id'),
            self::str($data, 'ip'),
            self::nullableStr($data, 'user_agent'),
            self::nullableStr($data, 'country'),
            self::int($data, 'first_seen'),
            self::int($data, 'last_seen'),
            self::int($data, 'duration_seconds'),
            self::int($data, 'playlist_requests'),
            self::int($data, 'segment_requests'),
            self::int($data, 'bytes_served'),
        );
    }
}
