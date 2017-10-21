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
    $loginQuery = self::$connection->prepare('SELECT mail FROM awww_users WHERE password=:pass');
    
    $password = self::saltHash($pass, $mail);
    
    $loginQuery->bindParam(':pass', $password, PDO::PARAM_STR, 64);
    $loginQuery->execute();
    
    if($loginQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $loginQuery->fetch();
    return $this->getUser($result['mail']);
  }
  
  /**
   * @return user from database
   */
  public function getUser($mail) {
    $userQuery = self::$connection->prepare('SELECT user_id, mail, wtype, name FROM ' . self::$prefix . 'users WHERE mail=:login');
    
    $userQuery->bindParam(':login', $mail, PDO::PARAM_INT);
    $userQuery->execute();
    
    if($userQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $userQuery->fetch();
    return new User($result['user_id'], $result['mail'], $result['wtype'], $result['name']);
  }
  
  
  /**
   * @return a null user with no access
   */
  public function getDefaultUser() {
    return new User(null, null, 0, null, null);
  }
  
  
  /**
   * Creates user
   *
   * @return false on error
   */
  public function createUser($mail, $password, $name, $type) {
    
    if($this->getUser($mail)->canLogin()) {
      return false;
    }
    
    $createUserQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'users (mail, password, name, wtype) OUTPUT INSERTED.user_id VALUES(:mail, :pass, :name, :type)');
    
    $createUserQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':pass', self::saltHash($password, $mail), PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':type', $type, PDO::PARAM_INT);
    
    $createUserQuery->execute();
    
    return $createUserQuery->fetch()['user_id'];
  }
  
  /**
   * Remove user
   *
   */
  public function removeUser($id, $mail) {
    // users table
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'users WHERE user_id=:uid AND mail=:mail');
    $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $removeQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
    $removeQuery->execute();
    
    // group assign table
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid');
    $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $removeQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
    $removeQuery->execute();
  }
  
  /**
   * Adds user (specified by mail) to the group of a specified id
   *
   * @return user id or false if already exists in group
   */
  public function addToGroup($mail, $group) {
    
    $user = $this->getUser($mail);
    
    $checkQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid AND group_id=:gid');
    $checkQuery->bindParam(':uid', $user->getID(), PDO::PARAM_INT);
    $checkQuery->bindParam(':gid', $group, PDO::PARAM_INT);
    $checkQuery->execute();
    
    if($checkQuery->rowCount() > 0) {
      return false;
    }
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'group_assign (user_id, group_id) VALUES(:uid, :gid)');
    $insertQuery->bindParam(':uid', $user->getID(), PDO::PARAM_INT);
    $insertQuery->bindParam(':gid', $group, PDO::PARAM_INT);
    $insertQuery->execute();
    
    return true;
  }
  
  /**
   * Removes user from group
   */
  public function removeFromGroup($id, $group) {
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid AND group_id=:gid');
    $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $removeQuery->bindParam(':gid', $group, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  /**
   * Adds group
   *
   * @return group id or false if already exists
   */
  public function addGroup($name) {
    $checkQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups WHERE group_name=:name');
    $checkQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $checkQuery->execute();
    
    if($checkQuery->rowCount() > 0) {
      return false;
    }
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'groups (group_name) OUTPUT INSERTED.group_id VALUES(:name)');
    $insertQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $insertQuery->execute();
    
    return $insertQuery->fetch()['group_id'];
  }
  
  /**
   * Remove group
   *
   */
  public function removeGroup($id) {
    // groups
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'groups WHERE group_id=:gid');
    $removeQuery->binParam(':gid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
    
    // group assign
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE group_id=:gid');
    $removeQuery->binParam(':gid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  /**
   * @return an array of Users in group
   *
   */
  public function getAllFromGroup($id, $privileged = true) {
    
    $privilegeCondition = self::$prefix . 'users.wtype > ' . User::$USER_TYPE['STUDENT'];
    
    $selectQuery = self::$connection->prepare('SELECT user_id, mail, name, wtype FROM ' . self::$prefix . 'users RIGHT JOIN ' . self::$prefix . 'group_assign ON ' . self::$prefix . 'group_assign.user_id = ' . self::$prefix . 'users.user_id WHERE ' . self::$prefix . 'group_assign.group_id = :gid ' . $privileged ? 'AND ' . $privilegeCondition : '' );
    
    $selectQuery->bindParam(':gid', $id, PDO::PARAM_INT);
    $selectQuery->execute();
    
    $result = array();
    
    foreach($selectQuery->fetchAll() as $row) {
      $user = new User($row['user_id'], $row['mail'], $row['wtype'], $row['name']);
      
      array_push($result, $user);
    }
    
    return $result;
  }
}