<?php
require_once 'User.php';

/**
 * Stores database credentials and handles
 * data exchange
 *
 */
final class UserData {
  
  private static $hashSaltPhrase  = 'AwW54l7';  // DO NOT CHANGE UNLESS YOU WANT TO
  private static $hashSaltMod     = 7;          // RESET PASSWORDS FOR EVERYONE
  
  private static $connection;
  
  // DATABASE LOGIN INFO
  private static $server   = 'localhost';
  private static $database = 'awww_data';
  private static $user     = 'awww';
  private static $pass     = 'test';
  private static $prefix   = 'awww_';
  
  
  /**
   * Get singleton instance
   *
   * @return UserData
   */
  public static function Instance() {
    static $instance = null;
    
    if ($instance === null) {
      $instance = new UserData();
    }
    
    return $instance;
  }
  
  /**
   * Private constructor for singleton
   *
   */
  private function __construct() {
    self::connect();
  }
  
  /**
   * Connect to the database
   *
   */
  private static function connect() {
    self::$connection = new PDO(
      'mysql:host=' . self::$server . 
      ';dbname=' . self::$database, 
      self::$user, self::$pass);
    
    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  
  /**
   * @return salt-hashed string
   */
  public static function saltHash($string, $requestUser) {
    
    $startPos = strlen($string) / 2 + strlen(self::$hashSaltMod) - 1;
    
    if($startPos >= strlen($string) % self::$hashSaltMod) {
      $startPos = - (strlen($string) % self::$hashSaltMod);
    }
    
    $newstr = substr_replace($string, self::$hashSaltPhrase, $startPos, 0);
    
    $startPos = (strlen($string) * strlen($requestUser)) % strlen($newstr);
    $newstr = substr_replace($newstr, $requestUser, $startPos, 0);
    
    return hash('sha256', $newstr);
  }
  
  /**
   * Sign in
   * @return signed in user
   */
  public function getSignedIn($mail, $pass) {
    $loginQuery = self::$connection->prepare('SELECT user_id FROM awww_users WHERE password=:pass');
    
    $password = self::saltHash($pass, $mail);
    
    $loginQuery->bindParam(':pass', $password, PDO::PARAM_STR, 64);
    $loginQuery->execute();
    
    if($loginQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $loginQuery->fetch();
    return $this->getUser($result['user_id']);
  }
  
  /**
   * @return user from database
   */
  public function getUser($id) {
    $userQuery = self::$connection->prepare('SELECT mail, wtype, wgroup, name FROM ' . self::$prefix . 'users WHERE user_id=:id');
    
    $userQuery->bindParam(':id', $id, PDO::PARAM_INT);
    $userQuery->execute();
    
    if($userQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $userQuery->fetch();
    return new User($id, $result['mail'], $result['wtype'], $result['wgroup'], $result['name']);
  }
  
  /**
   * @return a null user with no access
   */
  public function getDefaultUser() {
    return new User(null, null, 0, null, null);
  }
  
  public function register() {
    
  }
  
}