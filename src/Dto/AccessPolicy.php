<?php

declare(strict_types=1);

namespace Quikcast\Dto;

final readonly class AccessPolicy extends Dto
{
    /**
     * @param  list<string>  $countriesAllow
     * @param  list<string>  $countriesDeny
     */
    public function __construct(
        public int $ipAllowRules,
        public int $ipDenyRules,
        public int $userAgentAllowRules,
        public int $userAgentDenyRules,
        public array $countriesAllow,
        public array $countriesDeny,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::int($data, 'ip_allow_rules'),
            self::int($data, 'ip_deny_rules'),
            self::int($data, 'user_agent_allow_rules'),
            self::int($data, 'user_agent_deny_rules'),
            self::strList($data, 'countries_allow'),
            self::strList($data, 'countries_deny'),
        );
    }
}
