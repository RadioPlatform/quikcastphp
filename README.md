# quikcast-php

PHP client library for the [Quikcast](https://quikcast.example.com) native management API, built on [Guzzle](https://docs.guzzlephp.org/).

- Typed readonly DTOs (`ServerInfo`, `MountInfo`, `SourceInfo`, `ListenerInfo`, `StreamInfo`, `RenditionInfo`, `RelaySnapshot`, `Stats`, …) and enums for lifecycle states and error codes.
- One resource per API group: `server()`, `stats()`, `mounts()`, `sources()`, `listeners()`, `hls()`, `relays()`, `history()`, `metrics()`, plus `metadata()` (source operation) and `health()`.
- Typed exceptions per API error code; secrets are never logged or exposed.

Requires PHP `^8.2` and `guzzlehttp/guzzle` `^7.8`.

## Install

```sh
composer require quikcast/quikcast-php
```

## Quick start

```php
use Quikcast\QuikcastClient;

$client = QuikcastClient::create('http://127.0.0.1:9090', getenv('QUIKCAST_TOKEN'));

$server = $client->server()->get();
echo $server->state->value; // accepting | draining | shutting_down

$stats = $client->stats()->get();
printf("listeners=%d sources=%d\n", $stats->activeListeners, $stats->activeSources);

foreach ($client->mounts()->list() as $mount) {
    echo $mount->path.' '.($mount->source?->track->title ?? '(no source)').PHP_EOL;
}
```

> The management API runs on `admin_listen`, which must bind loopback. Forward it
> through a private proxy for remote administration, and supply the token from a
> file or secret store — never hardcode it:
>
> ```sh
> export QUIKCAST_ADMIN_URL='http://127.0.0.1:9090'
> export QUIKCAST_TOKEN="$(cat config/secrets/management-token)"
> ```

### Listeners (paginated)

```php
// Single page.
$page = $client->listeners()->page('/radio.mp3', limit: 100);

// Lazy page iterator (follows next_after until null).
foreach ($client->listeners()->paginate('/radio.mp3') as $page) {
    foreach ($page->items as $listener) {
        echo $listener->id.PHP_EOL;
    }
}

// Or collect everything (mount registries are bounded; fine for admin tooling).
$all = $client->listeners()->all('/radio.mp3');

// Disconnect one listener.
$client->listeners()->disconnect('0123456789abcdef0123456789abcdef');
```

### Sources

```php
// Null when the mount exists but has no connected source (API returns 200 null).
$source = $client->sources()->get('/radio.mp3');

// Pass generation to guard against acting on stale inspected state.
if ($source !== null) {
    $client->sources()->disconnect('/radio.mp3', $source->generation);
}
```

### Relays (202 snapshots, revision guards)

```php
foreach ($client->relays()->list() as $relay) {
    $updated = $client->relays()->restart($relay->mount, $relay->revision);
    echo $updated->state->value.PHP_EOL; // poll get() until settled
    // $client->relays()->stop($relay->mount, $relay->revision);
}
```

### HLS inspection

```php
foreach ($client->hls()->listStreams() as $stream) {
    foreach ($client->hls()->listRenditions($stream->name) as $rendition) {
        echo $rendition->name.' '.$rendition->format.PHP_EOL;
    }
}
```

### Listener history

```php
$history = $client->history()->get();                      // server-wide, last 60 minutes
$history = $client->history()->get('/radio.mp3', minutes: 180); // one mount, 1–1440 minutes

echo $history->peakListeners24h;            // highest concurrent listeners in 24h
echo $history->uniqueListenersEstimate24h;  // HyperLogLog estimate (~3% error)
foreach ($history->minutes as $m) {
    echo $m->startMs.' '.$m->peakListeners.PHP_EOL;
}
```

History is volatile (bounded memory, resets on restart).

### Policies and mount counters

`MountInfo` and `ServerInfo` expose sanitized access policy (`access`, `sourceAccess`,
`listenerAuth`, `hlsListenerAuth`), fallback target, dynamic flag, listener capacity,
per-reason disconnect counters, and an embedded `relay` snapshot. `ServerInfo::hasCapability()`
checks the advertised capability list.

### Draining

```php
$drained = $client->server()->drain(); // one-way until restart; idempotent
```

### Metadata updates (source operation — different credentials!)

`GET /api/metadata?mount=…&mode=updinfo&song=…` (or `artist=`/`title=`) is **not** a management endpoint:
it runs on the streaming listener, uses the mount's **source credentials** via
HTTP Basic auth, and returns ICY XML. Pass the listen URL, not the admin URL:

```php
$client->metadata()->updateSong(
    'http://127.0.0.1:8000', // listen port
    '/radio.mp3',
    'source',                // mount source username
    $sourceSecret,            // mount source secret
    'Artist - Title',
);
```

To set artist and title separately:

```php
$client->metadata()->updateTrack('http://127.0.0.1:8000', '/radio.mp3', 'source', $sourceSecret, 'Title', 'Artist');
```

### Prometheus metrics (no auth, admin listener)

```php
$text = $client->metrics()->text();
```

### Health probes (no auth)

```php
$client->health()->live();  // true while serving
$client->health()->ready(); // false (503) while draining
```

## Error handling

API errors use the `{error: {code, message}}` envelope and map to typed exceptions
(all extend `Quikcast\Exception\QuikcastException` with `->statusCode`, `->errorCode`, `->allow`):

| HTTP | Code | Exception |
|------|------|-----------|
| 401 | `unauthorized` | `UnauthorizedException` |
| 400 | `invalid_request` | `InvalidRequestException` |
| 400 | `invalid_pagination` | `InvalidPaginationException` |
| 404 | `mount_not_found` | `MountNotFoundException` |
| 404 | `listener_not_found` | `ListenerNotFoundException` |
| 404 | `hls_not_found` | `HlsNotFoundException` |
| 404 | `relay_not_found` | `RelayNotFoundException` |
| 404 | `endpoint_not_found` | `EndpointNotFoundException` |
| 405 | `method_not_allowed` | `MethodNotAllowedException` (`->allow` header) |
| 409 | `source_not_connected` | `SourceNotConnectedException` |
| 409 | `conflict` | `ConflictException` (stale generation/revision) |
| 503 | `server_draining` | `ServerDrainingException` |
| 500 | `internal_error` | `InternalErrorException` |

```php
use Quikcast\Exception\ConflictException;
use Quikcast\Exception\MountNotFoundException;

try {
    $client->sources()->disconnect('/radio.mp3', $generation);
} catch (ConflictException $e) {
    // Re-read the source generation and retry with fresh state.
} catch (MountNotFoundException $e) {
    // Unknown mount.
}
```

Network failures, timeouts, and malformed JSON throw `TransportException`.
Client-side validation (`limit` 1–200, 32-hex listener IDs/cursors, empty mount)
throws `InvalidArgumentException` before any request is sent.

## Mount path handling

Pass canonical (`/radio.mp3`) or bare (`radio.mp3`, `nested/radio.mp3`) paths —
the client normalizes to one leading slash and URL-encodes each segment, so
nested mounts never collide with reserved suffixes.

## Injecting your own Guzzle client

```php
use GuzzleHttp\Client;

$client = QuikcastClient::create($baseUrl, $token, new Client([
    'timeout' => 5.0,
]));
```

## Development

```sh
composer install
composer test   # PHPUnit
composer lint   # Pint --test
composer stan   # PHPStan (level max)
```

See `examples/basic-usage.php` and `examples/update-metadata.php`.
