<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use ChatServer\ChatServer;

$shortOpts = 'p::';

$options = getopt($shortOpts);
$port = isset($options['p']) && is_numeric($options['p']) ? $options['p'] : 8080;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    $port
);

$server->run();