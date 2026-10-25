<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class SourceAccessPolicy extends Dto
{
    public function __construct(
        public int $ipAllowRules,
        public int $ipDenyRules,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'ip_allow_rules'),
            self::int($data, 'ip_deny_rules'),
        );
    }
}
