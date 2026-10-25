<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\MountInfo;
use Quikcast\Support\MountPath;
use Quikcast\Transport\ManagementTransport;

final class MountsResource
{
    public function __construct(private readonly ManagementTransport $transport) {}

    /**
     * @return list<MountInfo>
     */
    public function list(): array
    {
        /** @var list<array<string, mixed>> $data */
        $data = $this->transport->request('GET', '/api/mounts');

        return array_map(MountInfo::fromArray(...), $data);
    }

    public function get(string $mount): MountInfo
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/mounts/'.MountPath::encodeForApi($mount));

        return MountInfo::fromArray($data);
    }
}
