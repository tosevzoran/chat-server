<?php

namespace ChatServer;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Message implements JsonSerializable {
  public const ACTION_NEW = 'new';
  public const ACTION_EDIT = 'edit';
  public const ACTION_DELETE = 'delete';
  public const ACTION_REGISTER = 'register';

  public $id;
  public $text;
  public $username;
  public $timestamp;
  public $action;

  public function __construct()
  {
    $uuid = Uuid::uuid4();

    $this->id = $uuid->toString();
    $this->text = '';
    $this->username = '';
    $this->action = '';
    $this->timestamp = time();
  }

  public static function createFromString(string $data, string $username) {
    $message = new Message();
    $decodedMessage = json_decode($data);

    if ($decodedMessage->action === self::ACTION_REGISTER) {
      $message->username = $decodedMessage->text;
      $message->action = $decodedMessage->action;

      return $message;
    }

    $message->text = $decodedMessage->text;
    $message->username = $username;
    $message->action = $decodedMessage->action;
    $message->timestamp = $decodedMessage->timestamp;

    return $message;
  }

  public static function createFromArray(array $data) {
    $message = new Message();

    $message->text = isset($data['text']) ?? $data['text'];
    $message->username = isset($data['username']) ?? $data['username'];
    $message->action = isset($data['action']) ?? $data['action'];
    $message->timestamp = isset($data['timestamp']) ?? $data['timestamp'];

    return $message;
  }

  public function jsonSerialize() {
    return [
      'id' => $this->id,
      'text' => $this->text,
      'username' => $this->username,
      'timestamp' => $this->timestamp,
      'action' => $this->action,
    ];
}
}