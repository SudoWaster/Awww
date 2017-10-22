<?php
require_once 'User.class.php';

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
    $loginQuery = self::$connection->prepare('SELECT mail FROM ' . self::$prefix . 'users WHERE password=:pass');
    
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
   * Update user info
   *
   */
  public function updateUser($id, $mail, $password, $name) {
    $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'users SET mail = :mail, password = :pass, name = :name WHERE user_id = :id');
    $updateQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
    $updateQuery->bindParam(':pass', self::saltHash($password, $mail), PDO::PARAM_STR, 64);
    $updateQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $updateQuery->bindParam(':id', $id, PDO::PARAM_INT);
    
    $updateQuery->execute();
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
    
    // user badges
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'user_badges WHERE user_id = :uid');
    $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  
  /**
   * @return an array of all users
   *
   */
  public function getAllUsers() {
    $selectQuery = self::$connection->prepare('SELECT user_id, mail, name, wtype FROM ' . self::$prefix . 'users');
    $selectQuery->execute();
    
    $result = array();
    
    foreach($selectQuery->fetchAll() as $row) {
      $user = new User($row['user_id'], $row['mail'], $row['wtype'], $row['name']);
      
      array_push($result, $user);
    }
    
    return $result;
  }
  
  
  /**
   * @return an array of groups the user belongs to
   */
  public function getUserGroups($uid) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups RIGHT JOIN ' . self::$prefix . 'group_assign ON ' . self::$prefix . 'groups.group_id = ' . self::$prefix . 'group_assign.group_id WHERE ' . self::$prefix . 'group_assign.user_id = :id');
    $selectQuery->bindParam(':id', $uid, PDO::PARAM_INT);
    
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return an array of user achievements
   *
   */
  public function getUserAchievements($uid, $groupID = false) {
    $groupCondition = ' AND ' . self::$prefix . 'achievements.group_id = :gid';
    
    $selectQuery = self::$connection->prepare('SELECT achievement_id, title, description FROM ' . self::$prefix . 'achievements RIGHT JOIN ' . self::$prefix . 'user_badges ON ' . self::$prefix . 'achievements.achievement_id = ' . self::$prefix . 'user_badges.achievement_id WHERE ' . self::$prefix . 'user_id = :uid ' . (!!$groupID ? $groupCondition : ''));
    $selectQuery->bindParam(':uid', $uid, PDO::PARAM_INT);
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return user progress in group
   */
  public function getUserProgress($uid, $groupID) {
    $result = 0;
    
    $selectQuery = self::$connection->prepare('SELECT COUNT(' . self::$prefix . 'achievements.achievement_id) FROM ' . self::$prefix . 'achievements RIGHT JOIN ' . self::$prefix . 'user_bagdes ON ' . self::$prefix . 'achievements.achievement_id = ' . self::$prefix . 'user_badges.achievement_id WHERE ' . self::$prefix . 'user_badges.user_id = :uid AND ' . self::$prefix . 'achievements.group_id = :gid');
    $selectQuery->bindParam(':uid', $uid, PDO::PARAM_INT);
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    $result = $selectQuery->fetch()['COUNT(' . self::$prefix . 'achievements.achievement_id)'];
    
    $selectQuery = self::$connection->prepare('SELECT COUNT(achievement_id) FROM ' . self::$prefix . 'achievements WHERE group_id = :gid');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    $all = $selectQuery->fetch()['COUNT(achievement_id)'];
    
    if($all == 0) {
      return 0;
    }
    
    return $result / $all;
  }
  
  /**
   * Adds user (specified by mail) to the group of a specified id
   *
   * @return user id or false if already exists in group or group is full
   */
  public function addToGroup($mail, $groupID, $updateVacancy = true) {
    
    if(!!$updateVacancy) {
      $checkQuery = self::$connection->prepare('SELECT vacancies FROM ' . self::$prefix . 'groups WHERE group_id = :gid');
      $checkQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
      $checkQuery->execute();
      
      if($checkQuery->fetch()['vacancies'] <= 0) {
        return false;
      }
      
      $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'groups SET vacancies = vacancies - 1 WHERE group_id = :gid');
      $updateQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
      $updateQuery->execute();
    }
    
    $user = $this->getUser($mail);
    
    $checkQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid AND group_id=:gid');
    $checkQuery->bindParam(':uid', $user->getID(), PDO::PARAM_INT);
    $checkQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $checkQuery->execute();
    
    if($checkQuery->rowCount() > 0) {
      return false;
    }
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'group_assign (user_id, group_id) VALUES(:uid, :gid)');
    $insertQuery->bindParam(':uid', $user->getID(), PDO::PARAM_INT);
    $insertQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
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
  public function addGroup($name, $description, $vacancies) {
    $checkQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups WHERE group_name=:name');
    $checkQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $checkQuery->execute();
    
    if($checkQuery->rowCount() > 0) {
      return false;
    }
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'groups (group_name, group_desc, vacancies) OUTPUT INSERTED.group_id VALUES(:name, :desc :vacancies)');
    $insertQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $insertQuery->bindParam(':desc', $description, PDO::PARAM_STR, 512);
    $insertQuery->bindParam(':vacancies', $vacancies, PDO::PARAM_INT);
    $insertQuery->execute();
    
    return $insertQuery->fetch()['group_id'];
  }
  
  
  /**
   * Update info and vacancies
   */
  public function updateGroup($id, $name, $description, $vacancies) {
    $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'groups SET group_name = :name, group_desc = :desc, vacancies = :vacancies WHERE group_id = :gid');
    $updateQuery->bindParam(':name', $name, PDO::PARAM_STR, 32);
    $updateQuery->bindParam(':desc', $description, PDO::PARAM_STR, 512);
    $updateQuery->bindParam(':vacancies', $vacancies, PDO::PARAM_INT);
    $updateQuery->bindParam(':gid', $id, PDO::PARAM_INT);
    $updateQuery->execute();
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
    
    // achievements
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'achievements WHERE group_id=:gid');
    $removeQuery->binParam(':gid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  
  /**
   * @return all active groups
   *
   */
  public function getAllGroups() {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups');
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return an array of Users in group
   *
   */
  public function getAllFromGroup($id, $privileged = true) {
    
    $privilegeCondition = self::$prefix . 'users.wtype > ' . User::$USER_TYPE['STUDENT'];
    
    // you may not like SQL, but it would be a lot harder using any other tool
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
  
  
  /**
   * Adds achivement
   *
   * @returns achievement id or false if exists
   */
  public function addAchievement($title, $description, $groupID) {
    $checkQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'achievements WHERE title = :title');
    $checkQuery->bindParam(':title', $title, PDO::PARAM_STR, 64);
    $checkQuery->execute();
    
    if($checkQuery->rowCount() > 0) {
      return false;
    }
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'achievements (title, description, group_id) OUTPUT INSERTED.achievement_id VALUES(:title, :description, :gid)');
    $insertQuery->bindParam(':title', $title, PDO::PARAM_STR, 64);
    $insertQuery->bindParam(':description', $description, PDO::PARAM_STR, 512);
    $insertQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $insertQuery->execute();
    
    return $insertQuery->fetch()['achievement_id'];
  }
  
  
  /**
   * Updates achievement info
   *
   */
  public function updateAchievement($id, $title, $description) {
    $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'achievements SET title = :title, description = :description WHERE achievement_id = :aid');
    $updateQuery->bindParam(':title', $title, PDO::PARAM_STR, 64);
    $updateQuery->bindParam(':description', $description, PDO::PARAM_STR, 512);
    $updateQuery->bindParam(':aid', $id, PDO::PARAM_INT);
    $updateQuery->execute();
  }
  
  
  /**
   * Removes achievement
   *
   */
  public function removeAchievement($id) {
    // achievements
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'achievements WHERE achievement_id = :aid');
    $removeQuery->bindParam(':aid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
    
    // user badges
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'user_badges WHERE achievement_id = :aid');
    $removeQuery->bindParam(':aid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  
  /**
   * @return an achievement 
   *
   */
  public function getAchievement($id) {
    $selectQuery = self::$connection->prepare('SELECT title, description FROM ' . self::$prefix . 'achievements WHERE achievement_id = :aid');
    $selectQuery->bindParam(':aid', $id, PDO::PARAM_ID);
    $selectQuery->execute();
    
    return $selectQuery->fetch();
  }
  
  
  /**
   * @return an array of group achievements
   */
  public function getGroupAchievements($group_id) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'achievements WHERE group_id = :gid');
    $selectQuery->bindParam(':gid', $group_id, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return an array of achievements
   *
   */
  public function getAllAchievements() {
    $selectQuery = self::$connection->prepare('SELECT achievement_id, title, description FROM ' . self::$prefix . 'achievements');
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
}