<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\ServerInfo;
use Quikcast\Transport\ManagementTransport;

final class ServerResource
{
    public function __construct(private readonly ManagementTransport $transport) {}

    public function get(): ServerInfo
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/server');

        return ServerInfo::fromArray($data);
    }

    public function drain(): ServerInfo
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('POST', '/api/server/drain');

        return ServerInfo::fromArray($data);
    }
}
