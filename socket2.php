<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
use React\Socket\LimitingServer;

$loop = Factory::create();

$server = new Server(8085, $loop);

$server = new LimitingServer($server, null);

$server->on('connection', function (ConnectionInterface $client) use ($server) {
    // whenever a new message comes in
    $client->on('data', function ($data) use ($client, $server) {
        // remove any non-word characters (just for the demo)
        $data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));

        // ignore empty messages
        if ($data === '') {
            return;
        }

        // prefix with client IP and broadcast to all connected clients
        $data = trim(parse_url($client->getRemoteAddress(), PHP_URL_HOST), '[]') . ': ' . $data . PHP_EOL;
        foreach ($server->getConnections() as $connection) {
            $connection->write($data);
        }
    });
});

$server->on('error', 'printf');

echo 'Listening on ' . $server->getAddress() . PHP_EOL;

$loop->run();