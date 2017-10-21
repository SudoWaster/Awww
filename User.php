<?php
class User {
  
  public static $USER_TYPES = array(
    0 => 'UNAUTHORISED',
    1 => 'STUDENT',
    2 => 'INSTRUCTOR',
    3 => 'ADMIN'
  );
  
  private $id;
  private $mail;
  private $type;
  private $group;
  private $name;
  
  public function __construct($id, $mail, $type, $group, $name) {
    $this->$id    = $id;
    $this->$mail  = $mail;
    $this->$type  = $type;
    $this->$group = $group;
    $this->$name  = $name;
  }
  
  public function getMail() {
    return $this->$mail;
  }
  
  public function getID() {
    return $this->$id;
  }
  
  public function getGroup() {
    return $this->$group;
  }
  
  public function getName() {
    return $this->$name;
  }
  
  public function isLogged() {
    return array_search($this->$type, self::$USER_TYPES) > 0;
  }
  
  public function isPrivileged() {
    return array_search($this->$type, self::$USER_TYPES) > 1;
  }
  
  public function isAdmin() {
    return array_search($this->$type, self::$USER_TYPES) >= 3;
  }
}