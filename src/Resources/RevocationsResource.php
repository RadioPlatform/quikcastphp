<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use InvalidArgumentException;
use Quikcast\Dto\Revocation;
use Quikcast\Transport\ManagementTransport;

/**
 * In-memory revocation of individual signed listener links (lost on restart, not shared between replicas).
 */
final readonly class RevocationsResource
{
    public function __construct(private ManagementTransport $transport) {}

    /**
     * @return list<Revocation>
     */
    public function list(): array
    {
        /** @var array{items?: list<array<string, mixed>>} $data */
        $data = $this->transport->request('GET', '/api/revocations');

        return array_map(Revocation::fromArray(...), $data['items'] ?? []);
    }

    /**
     * Stop one signed link from working before it expires. Pass the link's own sig and expires.
     *
     * @param  int  $expires  Unix seconds; must be in the future and within a week
     * @return list<Revocation> the full list after the change
     */
    public function revoke(string $signature, int $expires): array
    {
        if ($signature === '') {
            throw new InvalidArgumentException('signature must not be empty');
        }

        /** @var array{items?: list<array<string, mixed>>} $data */
        $data = $this->transport->request('POST', '/api/revocations', ['sig' => $signature, 'expires' => $expires]);

        return array_map(Revocation::fromArray(...), $data['items'] ?? []);
    }

    /**
     * Re-enable a previously revoked link.
     */
    public function restore(string $signature): void
    {
        if ($signature === '') {
            throw new InvalidArgumentException('signature must not be empty');
        }

        $this->transport->request('DELETE', '/api/revocations', ['sig' => $signature]);
    }
}
