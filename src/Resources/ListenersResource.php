<?php

declare(strict_types=1);

namespace Quikcast\Resources;

use InvalidArgumentException;
use Quikcast\Dto\ListenerInfo;
use Quikcast\Dto\ListenerPage;
use Quikcast\Support\MountPath;
use Quikcast\Transport\ManagementTransport;

final readonly class ListenersResource
{
    public function __construct(private ManagementTransport $transport) {}

    public function page(string $mount, int $limit = 100, ?string $after = null): ListenerPage
    {
        $canonical = MountPath::canonicalize($mount);

        if ($limit < 1 || $limit > 200) {
            throw new InvalidArgumentException('Listener page limit must be between 1 and 200.');
        }

        if ($after !== null && ! preg_match('/\A[0-9a-f]{32}\z/', $after)) {
            throw new InvalidArgumentException('Listener cursor must be 32 lowercase hex characters.');
        }

        $query = ['mount' => $canonical, 'limit' => $limit];
        if ($after !== null) {
            $query['after'] = $after;
        }

        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/listeners', $query);

        return ListenerPage::fromArray($data);
    }

    /**
     * Lazily iterate pages until next_after is null.
     *
     * @return iterable<ListenerPage>
     */
    public function paginate(string $mount, int $limit = 100): iterable
    {
        $after = null;

        do {
            $page = $this->page($mount, $limit, $after);
            yield $page;
            $after = $page->nextAfter;
        } while ($after !== null);
    }

    /**
     * @return list<ListenerInfo>
     */
    public function all(string $mount, int $limit = 100): array
    {
        $items = [];
        foreach ($this->paginate($mount, $limit) as $page) {
            array_push($items, ...$page->items);
        }

        return $items;
    }

    public function get(string $id): ListenerInfo
    {
        self::assertId($id);

        /** @var array<string, mixed> $data */
        $data = $this->transport->request('GET', '/api/listeners/'.rawurlencode($id));

        return ListenerInfo::fromArray($data);
    }

    public function disconnect(string $id): void
    {
        self::assertId($id);

        $this->transport->request('DELETE', '/api/listeners/'.rawurlencode($id));
    }

    private static function assertId(string $id): void
    {
        if (! preg_match('/\A[0-9a-f]{32}\z/', $id)) {
            throw new InvalidArgumentException('Listener ID must be 32 lowercase hex characters.');
        }
    }
}
