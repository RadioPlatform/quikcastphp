<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class ListenerAuth extends Dto
{
    public const NONE = 'none';

    public const SIGNED_URL = 'signed_url';

    /**
     * @param  string  $type  "none" or "signed_url"
     */
    public function __construct(
        public string $type,
        public ?int $maxTtlSeconds,
        public ?int $clockSkewSeconds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::str($data, 'type', self::NONE),
            self::nullableInt($data, 'max_ttl_seconds'),
            self::nullableInt($data, 'clock_skew_seconds'),
        );
    }

    public function isSigned(): bool
    {
        return $this->type === self::SIGNED_URL;
    }
}
