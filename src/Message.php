<?php

namespace ChatServer;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Message implements JsonSerializable {
  public const TYPE_JOIN = 'join';
  public const TYPE_LEAVE = 'leave';
  public const TYPE_GREETING = 'greeting';
  public const TYPE_MESSAGE = 'message';
  public const TYPE_EDIT = 'edit';
  public const TYPE_DELETE = 'delete';
  public const TYPE_USERNAME_UPDATE = 'username-update';

  public $id;
  public $text;
  public $username;
  public $timestamp;
  public $type;
  public $sender;
  public $isEdited;
  public $isDeleted;
  public $data;

  public function __construct()
  {
    $uuid = Uuid::uuid4();

    $this->id = $uuid->toString();
    $this->text = '';
    $this->username = '';
    $this->type = '';
    $this->timestamp = time();
    $this->sender = new User();
    $this->isEdited = false;
    $this->isDeleted = false;
    $this->data = [];
  }

  public static function createFromString(string $data) {
    $message = new Message();
    $decodedMessage = json_decode($data);

    $messageId = isset($decodedMessage->id) && $decodedMessage->id
      ? $decodedMessage->id
      : $message->id;

    $message->id = $messageId;
    $message->text = isset($decodedMessage->text) ? $decodedMessage->text : '';
    $message->username = isset($decodedMessage->username) ? $decodedMessage->username : '';
    $message->type = isset($decodedMessage->type) ? $decodedMessage->type : '';
    $message->timestamp = isset($message->timestamp) ? $message->timestamp : time();

    return $message;
  }

  public static function createFromArray(array $data) {
    $message = new Message();

    $messageId = isset($data['id']) && $data['id']
      ? $data['id']
      : $message->id;

    $sender = isset($data['sender']) && $data['sender'] ? $data['sender'] : null;

    $message->id = $messageId;
    $message->sender = $sender;
    $message->text = isset($data['text']) ? $data['text'] : '';
    $message->username = isset($data['username']) ? $data['username'] : '';
    $message->type = isset($data['type']) ? $data['type'] : '';
    $message->data = isset($data['data']) ? $data['data'] : '';
    $message->timestamp = isset($data['timestamp']) ? $data['timestamp'] : time();

    return $message;
  }

  public function jsonSerialize() {
    return [
      'id' => $this->id,
      'text' => $this->text,
      'username' => $this->username,
      'timestamp' => $this->timestamp,
      'type' => $this->type,
      'sender' => isset($this->sender) ? $this->sender->toArray() : [],
      'isEdited' => $this->isEdited,
      'isDeleted' => $this->isDeleted,
      'data' => $this->data,
    ];
  }
}