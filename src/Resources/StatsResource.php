<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use Quikcast\Dto\Stats;
use Quikcast\Transport\ManagementTransport;

final readonly class StatsResource
{
    public function __construct(private ManagementTransport $transport) {}

    public function get(): Stats
    {
        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/stats');

        return Stats::fromArray($data);
    }
}
