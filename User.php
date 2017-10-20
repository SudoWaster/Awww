<?php
class User {
  
  public static $USER_TYPES = array(
    0 => 'STUDENT',
    1 => 'INSTRUCTOR',
    2 => 'ADMIN'
  );
  
  private $name;
  private $type;
  private $id;
  private $group;
  
  public function __construct($name, $type, $id, $group) {
    $this->$name  = $name;
    $this->$type  = $type;
    $this->$id    = $id;
    $this->$group = $group;
  }
  
  public function getName() {
    return $name;
  }
  
  public function getID() {
    return $id;
  }
  
  public function getGroup() {
    return $group;
  }
  
  public function isPrivileged() {
    return array_search($type, self::$USER_TYPES) > 0;
  }
  
  public function isAdmin() {
    return array_search($type, self::$USER_TYPES) >= 2;
  }
}