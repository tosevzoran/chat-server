<?php

namespace ChatServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn) {
      echo "Connected".PHP_EOL;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
      echo "Message received".PHP_EOL;
    }

    public function onClose(ConnectionInterface $conn) {
      echo "Closed".PHP_EOL;
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
      echo "Error".PHP_EOL;
    }
}