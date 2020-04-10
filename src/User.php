<?php

namespace ChatServer;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

class User implements JsonSerializable {
  public $id;
  public $username;
  public $isDeleted;

  public function __construct()
  {
    $uuid = Uuid::uuid4();

    $this->id = $uuid->toString();
    $this->username = '';
    $this->isDeleted = false;
  }

  public static function createFromArray(array $data) {
    $user = new User();

    $user->username = isset($data['username']) ? $data['username'] : '';

    return $user;
  }

  public function toArray() {
    return [
      'id' => $this->id,
      'username' => $this->username,
      'isDeleted' => $this->isDeleted,
    ];
  }

  public function jsonSerialize() {
    return $this->toArray();
  }
}