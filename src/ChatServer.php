<?php

namespace ChatServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Ramsey\Uuid\Uuid;

class ChatServer implements MessageComponentInterface
{
  private $connections;

  public function __construct()
  {
    $this->connections = new SplObjectStorage();
  }

  public function onOpen(ConnectionInterface $conn) {
    $uuid = Uuid::uuid4();
    $username = 'Anonymous_' . substr($uuid->toString(), 0, 5);

    echo "{$username} Connected" . PHP_EOL;

    $userJoined = Message::createFromArray([
      'username' => 'Meetingbot',
      'action' => Message::ACTION_REGISTER,
      'text' => "{$username} joined.",
    ]);

    $greeting = Message::createFromArray([
      'action' => Message::ACTION_GREETING,
      'username' => $username,
    ]);

    $this->sendTo($greeting, $conn);
    $this->setConnectionData($conn, ['username' => $username]);
    $this->sendToAll($userJoined, $conn);
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
        // TODO: send update notificaiotn
        break;
      case Message::ACTION_NEW:
        $connectionData = $this->getConnectionData($from);

        if (empty($message->username)) {
          $message->username = $connectionData['username'];
        }

        $this->sendToAll($message);
        break;
      case Message::ACTION_EDIT:
        break;
      case Message::ACTION_DELETE:
        break;
      default:
        break;
    }
  }

  private function sendToAll(Message $m, ConnectionInterface $exclude = null) {
    echo 'Sending message ' . json_encode($m) . PHP_EOL;

    foreach ($this->connections as $connection) {
      if ($connection !== $exclude) {
        $this->sendTo($m, $connection);
      }
    }
  }

  private function sendTo(Message $m, ConnectionInterface $to) {
    $to->send(json_encode($m));
  }

  private function setConnectionData(ConnectionInterface $c, $data) {
    $this->connections->offsetSet($c, $data);
  }

  private function getConnectionData(ConnectionInterface $c) {
      return $this->connections->offsetGet($c);
  }
}