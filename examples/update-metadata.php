<?php

declare(strict_types=1);

use Quikcast\QuikcastClient;

require __DIR__.'/../vendor/autoload.php';

// NOTE: the metadata operation runs on the streaming listener, NOT the
// management API. It uses the mount's source credentials via HTTP Basic auth.
$listenUrl = (string) getenv('QUIKCAST_LISTEN_URL');
$mount = (string) getenv('QUIKCAST_MOUNT');
$sourceUser = (string) getenv('QUIKCAST_SOURCE_USER');
$sourceSecret = (string) getenv('QUIKCAST_SOURCE_SECRET');
$song = $argv[1] ?? '';

if ($listenUrl === '' || $mount === '' || $sourceUser === '' || $sourceSecret === '' || $song === '') {
    fwrite(STDERR, "Usage: QUIKCAST_LISTEN_URL=... QUIKCAST_MOUNT=/radio.mp3 QUIKCAST_SOURCE_USER=source QUIKCAST_SOURCE_SECRET=... php examples/update-metadata.php 'Artist - Title'\n");
    exit(1);
}

$client = QuikcastClient::create('http://127.0.0.1:9090', 'unused-for-metadata');
$client->metadata()->updateSong($listenUrl, $mount, $sourceUser, $sourceSecret, $song);

echo "Metadata updated.\n";
