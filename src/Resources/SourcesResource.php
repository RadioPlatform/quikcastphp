<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\SourceInfo;
use Quikcast\Support\MountPath;
use Quikcast\Transport\ManagementTransport;

final readonly class SourcesResource
{
    public function __construct(private ManagementTransport $transport) {}

    /**
     * Returns null when the mount exists but has no connected source
     * (the API returns 200 with JSON null).
     */
    public function get(string $mount): ?SourceInfo
    {
        $data = $this->transport->request('GET', '/api/sources/'.MountPath::encodeForApi($mount));

        if ($data === null) {
            return null;
        }

        /** @var array<string, mixed> $data */
        return SourceInfo::fromArray($data);
    }

    public function disconnect(string $mount, ?int $generation = null): void
    {
        $query = [];
        if ($generation !== null) {
            $query['generation'] = $generation;
        }

        $this->transport->request('DELETE', '/api/sources/'.MountPath::encodeForApi($mount), $query);
    }
}
