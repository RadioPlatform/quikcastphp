<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\RelaySnapshot;
use Quikcast\Support\MountPath;
use Quikcast\Transport\ManagementTransport;

final class RelaysResource
{
    public function __construct(private readonly ManagementTransport $transport) {}

    /**
     * @return list<RelaySnapshot>
     */
    public function list(): array
    {
        /** @var list<array<string, mixed>> $data */
        $data = $this->transport->request('GET', '/api/relays');

        return array_map(RelaySnapshot::fromArray(...), $data);
    }

    public function get(string $mount): RelaySnapshot
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/relays/'.MountPath::encodeForApi($mount));

        return RelaySnapshot::fromArray($data);
    }

    public function restart(string $mount, ?int $revision = null): RelaySnapshot
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request(
            'POST',
            '/api/relays/'.MountPath::encodeForApi($mount),
            $revision !== null ? ['revision' => $revision] : []
        );

        return RelaySnapshot::fromArray($data);
    }

    public function stop(string $mount, ?int $revision = null): RelaySnapshot
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request(
            'DELETE',
            '/api/relays/'.MountPath::encodeForApi($mount),
            $revision !== null ? ['revision' => $revision] : []
        );

        return RelaySnapshot::fromArray($data);
    }
}
