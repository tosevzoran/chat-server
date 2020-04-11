<?php

namespace ChatServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Ramsey\Uuid\Uuid;

class ChatServer implements MessageComponentInterface
{
  private $connections;
  private $messageHistory;

  public function __construct()
  {
    $this->connections = new SplObjectStorage();
    $this->messageHistory = [];
  }

  public function onOpen(ConnectionInterface $conn) {
    parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);

    $userId = isset($queryParams['userId']) ? $queryParams['userId'] : '';
    [$user, $prevConnection] = $this->getUserAndConnection($userId);

    if (empty($user)) {
      $user = $this->createNewUser();
    }

    $message = $user->isDeleted ? "{$user->username} Reconnected" : "{$user->username} Connected.";
    $user->isDeleted = false;

    $this->connections->offsetUnset($prevConnection);

    $this->setConnectionData($conn, ['user' => $user]);

    $this->sendGreetings($conn);
    $this->sendJoinNotification($conn);

    echo $message . PHP_EOL;
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    $this->handleMessage($from, $msg);
  }

  public function onClose(ConnectionInterface $conn) {
    $connectionData = $this->getConnectionData($conn);
    $user = $connectionData['user'];
    $user->isDeleted = true;

    $messageText = "{$user->username} left.";
    $message = Message::createFromArray([
      'text' => $messageText,
      'username' => 'Meetingbot',
      'type' => Message::TYPE_LEAVE,
      'user' => $user,
    ]);

    $this->setConnectionData($conn, ['user' => $user]);

    $this->sendToAll($message, $conn);

    echo $messageText . PHP_EOL;
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "Error".PHP_EOL;
    var_export($e->getTraceAsString());
  }

  private function handleMessage(ConnectionInterface $from, $msg) {
    $message = Message::createFromString($msg);

    $connectionData = $this->getConnectionData($from);
    $user = $connectionData['user'];

    switch ($message->type) {
      case Message::TYPE_MESSAGE:
        $message->user = $user;
        $message->username = $user->username;

        $this->messageHistory[$message->id] = $message;
        $this->sendToAll($message);

        break;
      case Message::TYPE_EDIT:
        $existingMessage = isset($message->id) ? $this->messageHistory[$message->id] : null;

        if (!empty($existingMessage)) {
          $existingMessage->text = $message->text;
          $existingMessage->isEdited = true;

          $this->messageHistory[$existingMessage->id] = $existingMessage;

          $this->sendToAll($existingMessage);
        }

        break;
      default:
        break;
    }
  }

  private function sendGreetings(ConnectionInterface $to) {
    $connectionData = $this->getConnectionData($to);
    $loggedUser = $connectionData['user'];
    $connectedUsers = $this->getUsers();

    $message = [
      'type' => Message::TYPE_GREETING,
      'loggedUser' => $loggedUser,
      'messageHistory' => $this->messageHistory,
      'connectedUsers' => $connectedUsers,
    ];

    $to->send(json_encode($message));
  }

  private function sendJoinNotification(ConnectionInterface $conn) {
    $connectionData = $this->getConnectionData($conn);
    $user = $connectionData['user'];

    $userJoined = Message::createFromArray([
      'username' => 'Meetingbot',
      'type' => Message::TYPE_JOIN,
      'text' => "{$user->username} joined.",
      'user' => $user,
    ]);

    $this->sendToAll($userJoined, $conn);
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

  private function createNewUser() {
    $uuid = Uuid::uuid4();
    $username = 'Anonymous_' . substr($uuid->toString(), 0, 5);

    return User::createFromArray([
      'id' => $uuid->toString(),
      'username' => $username,
    ]);
  }

  private function getUsers() {
    $users = [];

    foreach ($this->connections as $connection) {
      $connectionData = $this->getConnectionData($connection);
      $users[] = $connectionData['user'];
    }

    return $users;
  }

  private function getUserAndConnection($userId) {
    foreach ($this->connections as $connection) {
      $connectionData = $this->getConnectionData($connection);
      $user = $connectionData['user'];

      if ($user->id === $userId) {
        return [$user, $connection];
      }
    }

    return [null, null];
  }
}