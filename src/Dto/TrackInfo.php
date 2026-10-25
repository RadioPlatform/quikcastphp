<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class TrackInfo extends Dto
{
    public function __construct(
        public int $revision,
        public string $title,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'revision'),
            self::str($data, 'title'),
        );
    }
}
