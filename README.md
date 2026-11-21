# quikcast/quikcast

PHP client for the [Quikcast](https://quikcastdocs.streamafrica.net/) management API. It returns typed, read-only objects and throws a specific exception for every API error.

## Install

Requires PHP 8.4 or newer.

```sh
composer require quikcast/quikcast
```

## Usage

```php
use Quikcast\QuikcastClient;

// The admin address and your management token.
$client = QuikcastClient::create('http://127.0.0.1:9090', getenv('QUIKCAST_TOKEN'));

$stats = $client->stats()->get();
echo $stats->activeListeners.' listening';

foreach ($client->mounts()->list() as $mount) {
    echo $mount->path.PHP_EOL;
}

// Disconnect a listener, ban a range, update the track title, and more.
$client->bans()->create('203.0.113.0/24', ttlSeconds: 3600, kick: true);
```

## Documentation

Every resource, the error types and more examples are in the full docs:
<https://quikcastdocs.streamafrica.net/clients/php>

## Development

```sh
composer install
composer test   # PHPUnit
composer lint   # Pint
composer stan   # PHPStan
```

## License

MIT
