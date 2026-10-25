<?php

declare(strict_types=1);

use Quikcast\QuikcastClient;

require __DIR__.'/../vendor/autoload.php';

$baseUrl = (string) getenv('QUIKCAST_ADMIN_URL');
$token = (string) getenv('QUIKCAST_TOKEN');

if ($baseUrl === '' || $token === '') {
    fwrite(STDERR, "Set QUIKCAST_ADMIN_URL and QUIKCAST_TOKEN environment variables.\n");
    exit(1);
}

$client = QuikcastClient::create($baseUrl !== '' ? $baseUrl : 'http://127.0.0.1:9090', $token);

// Server + stats.
$server = $client->server()->get();
printf("Server %s %s (%s)\n", $server->name, $server->version, $server->state->value);

$stats = $client->stats()->get();
printf("Listeners: %d, sources: %d\n", $stats->activeListeners, $stats->activeSources);

// Mounts.
foreach ($client->mounts()->list() as $mount) {
    printf(
        "%s state=%s listeners=%d source=%s\n",
        $mount->path,
        $mount->state->value,
        $mount->activeListeners,
        $mount->source?->track->title ?? '(no source)'
    );
}

// Paginated listeners for a mount.
foreach ($client->listeners()->paginate('/radio.mp3') as $page) {
    foreach ($page->items as $listener) {
        printf("listener %s bytes=%d\n", $listener->id, $listener->bytesSent);
    }
}

// Guarded source disconnect (read generation first to avoid acting on stale state).
$source = $client->sources()->get('/radio.mp3');
if ($source !== null) {
    $client->sources()->disconnect('/radio.mp3', $source->generation);
    echo "Source disconnect requested.\n";
}

// Relay restart with revision guard.
foreach ($client->relays()->list() as $relay) {
    $updated = $client->relays()->restart($relay->mount, $relay->revision);
    printf("relay %s revision=%d state=%s\n", $updated->mount, $updated->revision, $updated->state->value);
}
