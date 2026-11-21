<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use InvalidArgumentException;
use Quikcast\Dto\Ban;
use Quikcast\Dto\BanResult;
use Quikcast\Transport\ManagementTransport;

/**
 * In-memory listener bans (lost on restart, not shared between replicas).
 */
final readonly class BansResource
{
    public function __construct(private ManagementTransport $transport) {}

    /**
     * @return list<Ban>
     */
    public function list(): array
    {
        /** @var array{items?: list<array<string, mixed>>} $data */
        $data = $this->transport->request('GET', '/api/bans');

        return array_map(Ban::fromArray(...), $data['items'] ?? []);
    }

    /**
     * Refuse an address or CIDR range. Banning the same $ip text again replaces its entry.
     *
     * @param  int|null  $ttlSeconds  1 to 2,592,000 (server default 3,600)
     * @param  string|null  $reason  up to 128 bytes
     * @param  bool  $kick  also disconnect connected listeners in the range
     */
    public function create(string $ip, ?int $ttlSeconds = null, ?string $reason = null, bool $kick = false): BanResult
    {
        if ($ip === '') {
            throw new InvalidArgumentException('ip must not be empty');
        }

        if ($ttlSeconds !== null && ($ttlSeconds < 1 || $ttlSeconds > 2_592_000)) {
            throw new InvalidArgumentException('ttlSeconds must be between 1 and 2592000');
        }

        if ($reason !== null && strlen($reason) > 128) {
            throw new InvalidArgumentException('reason must be at most 128 bytes');
        }

        $query = ['ip' => $ip];

        if ($ttlSeconds !== null) {
            $query['ttl_seconds'] = $ttlSeconds;
        }

        if ($reason !== null && $reason !== '') {
            $query['reason'] = $reason;
        }

        if ($kick) {
            $query['kick'] = 'true';
        }

        /** @var array<string, mixed> $data */
        $data = $this->transport->request('POST', '/api/bans', $query);

        return BanResult::fromArray($data);
    }

    /**
     * Lift a ban, using the same $ip text it was created with.
     */
    public function lift(string $ip): void
    {
        if ($ip === '') {
            throw new InvalidArgumentException('ip must not be empty');
        }

        $this->transport->request('DELETE', '/api/bans', ['ip' => $ip]);
    }
}
