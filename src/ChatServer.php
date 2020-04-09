<?php

namespace ChatServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class ChatServer implements MessageComponentInterface
{
  private $connections;

  public function __construct()
  {
    $this->connections = new SplObjectStorage();
  }

  public function onOpen(ConnectionInterface $conn) {
    echo 'Connected' . PHP_EOL;

    $this->setConnectionData($conn, []);
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    $this->handleMessage($from, $msg);
  }

  public function onClose(ConnectionInterface $conn) {
    echo "Closed".PHP_EOL;
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "Error".PHP_EOL;
    var_export($e->getTraceAsString());
  }

  private function handleMessage(ConnectionInterface $from, $msg) {
    $message = Message::createFromString($msg);

    switch ($message->action) {
      case Message::ACTION_REGISTER:
        $this->setConnectionData($from, ['username' => $message->username]);

        $userJoined = Message::createFromArray([
          'username' => 'Meetingbot',
          'action' => Message::ACTION_REGISTER,
          'text' => "{$message->username} joined.",
        ]);

        $this->sendToAll($from, $userJoined);
        break;
      case Message::ACTION_NEW:
        $this->sendToAll($from, $message);
        break;
      case Message::ACTION_EDIT:
        break;
      case Message::ACTION_DELETE:
        break;
      default:
        break;
    }
  }

  private function sendToAll(ConnectionInterface $conn, Message $m) {
    foreach ($this->connections as $connection) {
      if ($connection !== $conn) {
        $connection->send(json_encode($m));
      }
    }
  }

  private function setConnectionData(ConnectionInterface $c, $data) {
    $this->connections->offsetSet($c, $data);
  }

  private function getConnectionData(ConnectionInterface $c) {
      return $this->connections->offsetGet($c);
  }
}