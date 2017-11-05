<?php
class User {
  
  public static $USER_TYPES = array(
    'UNAUTHORISED'  => 0,
    'STUDENT'       => 1,
    'INSTRUCTOR'    => 2,
    'ADMIN'         => 3
  );
  
  public static $PRIVILEGED_GROUP = 'PRIVILEGED';
  
  private $id;
  private $mail;
  private $type;
  private $name;
  private $lastname;
  
  public function __construct($id, $mail, $type, $name, $lastname) {
    $this->id       = $id;
    $this->mail     = $mail;
    $this->type     = $type;
    $this->name     = $name;
    $this->lastname = $lastname;
  }
  
  public function getMail() {
    return $this->mail;
  }
  
  public function getID() {
    return $this->id;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getLastName() {
    return $this->lastname;
  }
  
  public function getFullName() {
    return $this->name . ' ' . $this->lastname;
  }
  
  public function canLogin() {
    return $this->type > self::$USER_TYPES['UNAUTHORISED'];
  }
  
  public function isPrivileged() {
    return $this->type > self::$USER_TYPES['STUDENT'];
  }
  
  public function isAdmin() {
    return $this->type >= self::$USER_TYPES['ADMIN'];
  }
  
  public function getTypeDesc() {
    return array_search($this->type, self::$USER_TYPES);
  }
}