<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class SourceMetadata extends Dto
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $genre,
        public ?string $url,
        public ?bool $public,
        public ?int $declaredBitrateKbps,
        public ?int $declaredSampleRateHz,
        public ?int $declaredChannels,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::nullableStr($data, 'name'),
            self::nullableStr($data, 'description'),
            self::nullableStr($data, 'genre'),
            self::nullableStr($data, 'url'),
            self::nullableBool($data, 'public'),
            self::nullableInt($data, 'declared_bitrate_kbps'),
            self::nullableInt($data, 'declared_sample_rate_hz'),
            self::nullableInt($data, 'declared_channels'),
        );
    }
}
