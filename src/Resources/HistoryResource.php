<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use InvalidArgumentException;
use Quikcast\Dto\History;
use Quikcast\Support\MountPath;
use Quikcast\Transport\ManagementTransport;

final class HistoryResource
{
    public const MAX_MINUTES = 1440;

    public function __construct(private readonly ManagementTransport $transport) {}

    /**
     * Volatile listener history for the whole server, or one mount when $mount is given.
     *
     * @param string|null $mount
     * @param int|null $minutes per-minute window, 1–1440 (server default 60)
     * @return History
     */
    public function get(?string $mount = null, ?int $minutes = null): History
    {
        if ($minutes !== null && ($minutes < 1 || $minutes > self::MAX_MINUTES)) {
            throw new InvalidArgumentException('History minutes must be between 1 and '.self::MAX_MINUTES.'.');
        }

        $query = [];
        if ($mount !== null) {
            $query['mount'] = MountPath::canonicalize($mount);
        }
        if ($minutes !== null) {
            $query['minutes'] = $minutes;
        }

        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/history', $query);

        return History::fromArray($data);
    }
}
