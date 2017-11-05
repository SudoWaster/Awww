<?php
require_once __DIR__ . '/User.class.php';

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
  private static $server;
  private static $database;
  private static $user;
  private static $pass;
  private static $prefix;
  
  
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
    $conf     = parse_ini_file(__DIR__ . '/dbconfig.ini', true);
  
    self::$server   = $conf['DB']['SERVER'];
    self::$database = $conf['DB']['BASE'];
    self::$user     = $conf['DB']['USER'];
    self::$pass     = $conf['DB']['PASS'];
    self::$prefix   = $conf['DB']['PREFIX'];
    
    self::connect();
  }
  
  
  /**
   * Connect to the database
   *
   */
  private static function connect() {
    self::$connection = new PDO(
      'mysql:host=' . self::$server . ';' .
      'dbname=' . self::$database . ';' .
      'charset=utf8', 
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
  public function getUserByID($id) {
    $userQuery = self::$connection->prepare('SELECT user_id, mail, wtype, name, lastname FROM ' . self::$prefix . 'users WHERE user_id=:uid');
    
    $userQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $userQuery->execute();
    
    if($userQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $userQuery->fetch();
    return new User($result['user_id'], $result['mail'], $result['wtype'], $result['name'], $result['lastname']);
  }
  
  
  
  /**
   * @return user from database
   */
  public function getUser($mail) {
    $userQuery = self::$connection->prepare('SELECT user_id, mail, wtype, name, lastname FROM ' . self::$prefix . 'users WHERE mail=:login');
    
    $userQuery->bindParam(':login', $mail, PDO::PARAM_STR, 64);
    $userQuery->execute();
    
    if($userQuery->rowCount() <= 0) {
      return $this->getDefaultUser();
    }
    
    $result = $userQuery->fetch();
    return new User($result['user_id'], $result['mail'], $result['wtype'], $result['name'], $result['lastname']);
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
    
    $createUserQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'users (mail, password, name, wtype) VALUES(:mail, :pass, :name, :type)');
    
    $createUserQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':pass', self::saltHash($password, $mail), PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $createUserQuery->bindParam(':type', $type, PDO::PARAM_INT);
    
    $createUserQuery->execute();
    
    return self::$connection->lastInsertId();
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
    
    try {
    
      // group assign table
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid');
      $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
      $removeQuery->bindParam(':mail', $mail, PDO::PARAM_STR, 64);
      $removeQuery->execute();

      // user badges
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'user_badges WHERE user_id = :uid');
      $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
      $removeQuery->execute();

      // presence
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'presence WHERE user_id = :uid');
      $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
      $removeQuery->execute();
      
    } catch (Exception $e) {}
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
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups g RIGHT JOIN ' . self::$prefix . 'group_assign ga ON g.group_id = ga.group_id WHERE ga.user_id = :id');
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
    
    $selectQuery = self::$connection->prepare('SELECT a.achievement_id, title, description FROM ' . self::$prefix . 'achievements a RIGHT JOIN ' . self::$prefix . 'user_badges ub ON a.achievement_id = ub.achievement_id WHERE ub.user_id = :uid ' . (!!$groupID ? $groupCondition : ''));
    
    $selectQuery->bindParam(':uid', $uid, PDO::PARAM_INT);
    
    if(!!$groupID) {
      $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    }
    
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return user progress in group
   */
  public function getUserProgress($uid, $groupID) {
    $result = 0;
    
    $selectQuery = self::$connection->prepare('SELECT COUNT(a.achievement_id) as cnt FROM ' . self::$prefix . 'achievements a RIGHT JOIN ' . self::$prefix . 'user_badges ub ON a.achievement_id = ub.achievement_id WHERE ub.user_id = :uid AND a.group_id = :gid');
    $selectQuery->bindParam(':uid', $uid, PDO::PARAM_INT);
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    $result = $selectQuery->fetch()['cnt'];
    
    $selectQuery = self::$connection->prepare('SELECT COUNT(achievement_id) as cnt FROM ' . self::$prefix . 'achievements WHERE group_id = :gid');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    $all = $selectQuery->fetch()['cnt'];
    
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
  public function removeFromGroup($id, $group, $updateVacancy = true) {
    if(!!$updateVacancy) {
      $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'groups SET vacancies = vacancies + 1 WHERE group_id = :gid');
      $updateQuery->bindParam(':gid', $group, PDO::PARAM_INT);
      $updateQuery->execute();
    }
    
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE user_id=:uid AND group_id=:gid');
    $removeQuery->bindParam(':uid', $id, PDO::PARAM_INT);
    $removeQuery->bindParam(':gid', $group, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  
  /**
   * @return whether user is in group
   */
  public function isInGroup($uid, $group) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_assign WHERE user_id = :uid AND group_id = :gid');
    $selectQuery->bindParam(':uid', $uid, PDO::PARAM_INT);
    $selectQuery->bindParam(':gid', $group, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->rowCount() > 0;
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
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'groups (group_name, group_desc, vacancies) VALUES(:name, :desc, :vacancies)');
    $insertQuery->bindParam(':name', $name, PDO::PARAM_STR, 64);
    $insertQuery->bindParam(':desc', $description, PDO::PARAM_STR, 512);
    $insertQuery->bindParam(':vacancies', $vacancies, PDO::PARAM_INT);
    $insertQuery->execute();
    
    return self::$connection->lastInsertId();
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
    $removeQuery->bindParam(':gid', $id, PDO::PARAM_INT);
    $removeQuery->execute();
    
    try {
      // group assign
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_assign WHERE group_id=:gid');
      $removeQuery->bindParam(':gid', $id, PDO::PARAM_INT);
      $removeQuery->execute();

      // achievements
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'achievements WHERE group_id=:gid');
      $removeQuery->bindParam(':gid', $id, PDO::PARAM_INT);
      $removeQuery->execute();

      // posts
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_posts WHERE group_id=:gid');
      $removeQuery->bindParam(':gid', $id, PDO::PARAM_INT);
      $removeQuery->execute();

      // presence
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'presence WHERE group_id=:gid');
      $removeQuery->bindParam(':gid', $id, PDO::PARAM_INT);
      $removeQuery->execute();
    }
    catch (Exception $e) {}
  }
  
  
  /**
   * @return all groups
   *
   */
  public function getAllGroups() {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups');
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }

  
  /**
   * @return all groups
   *
   */
  public function getGroup($gid) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'groups WHERE group_id = :gid');
    $selectQuery->bindParam(':gid', $gid, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetch();
  }
  
  
  /**
   * @return an array of Users in group
   *
   */
  public function getAllFromGroup($id, $privileged = false) {
    
    $privilegeCondition = 'u.wtype > ' . User::$USER_TYPES['STUDENT'];
    $else = 'u.wtype = ' . User::$USER_TYPES['STUDENT'];
    
    // you may not like SQL, but it would be a lot harder using any other tool
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_assign ga LEFT JOIN ' . self::$prefix . 'users u ON ga.user_id = u.user_id WHERE group_id = :gid AND ' . ($privileged ? $privilegeCondition : $else) . ' ORDER BY lastname ASC');
    
    $selectQuery->bindParam(':gid', $id, PDO::PARAM_INT);
    $selectQuery->execute();
    
    $result = array();
    $data = $selectQuery->fetchAll();
    
    foreach($data as $row) {
      
      $user = new User($row['user_id'], $row['mail'], $row['wtype'], $row['name'], $row['lastname']);
      
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
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'achievements (title, description, group_id) VALUES(:title, :description, :gid)');
    $insertQuery->bindParam(':title', $title, PDO::PARAM_STR, 64);
    $insertQuery->bindParam(':description', $description, PDO::PARAM_STR, 512);
    $insertQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $insertQuery->execute();
    
    return self::$connection->lastInsertId();
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
    
    try {
      // user badges
      $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'user_badges WHERE achievement_id = :aid');
      $removeQuery->bindParam(':aid', $id, PDO::PARAM_INT);
      $removeQuery->execute();
    } catch(Exception $e) {}
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
  
  
  /**
   * Adds group post
   *
   * @returns post id or false if not allowed
   */
  public function addPost($opMAIL, $groupID, $post) {
    
    $op = $this->getUser($opMAIL);
    if(!$op->isPrivileged()) {
      return false;
    }
    
    $date = date('o-m-d');
    $opID = $op->getID();
    
    $insertQuery = self::$connection->prepare('INSERT INTO ' . self::$prefix . 'group_posts (group_id, date, op_id, post_content) VALUES(:gid, :date, :op, :post)');
    $insertQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $insertQuery->bindParam(':date', $date, PDO::PARAM_STR);
    $insertQuery->bindParam(':op', $opID, PDO::PARAM_INT);
    $insertQuery->bindParam(':post', $post, PDO::PARAM_STR);
    
    $insertQuery->execute();
    
    return self::$connection->lastInsertId();
  }
  
  /**
   * Update post
   * @return false if not allowed
   */
  public function updatePost($opMAIL, $group, $postID, $post) {
    $op = $this->getUser($opMAIL);
    if(!$op->isPrivileged() || !$this->isInGroup($op->getID(), $group)) {
      return false;  
    } 
    
    $date = date('o-m-d');
    $opID = $op->getID();
    
    $updateQuery = self::$connection->prepare('UPDATE ' . self::$prefix . 'group_posts SET date = :date, op_id = :op, post_content = :post WHERE post_id = :pid');
    $updateQuery->bindParam(':date', $date, PDO::PARAM_STR);
    $updateQuery->bindParam(':op', $opID, PDO::PARAM_INT);
    $updateQuery->bindParam(':post', $post, PDO::PARAM_STR);
    $updateQuery->bindParam(':pid', $postID, PDO::PARAM_INT);
    $updateQuery->execute();
    
    return true;
  }
  
  
  /**
   * @return particular post
   *
   */
  public function getPost($postID) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_posts WHERE post_id = :pid');
    $selectQuery->bindParam(':pid', $postID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetch();
  }
  
  
  /**
   * @return posts in group
   *
   */
  public function getPosts($groupID) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_posts WHERE group_id = :gid ORDER BY date DESC');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return newest post in group
   *
   */
  public function getNewestPost($groupID) {
    $selectQuery = self::$connection->prepare('SELECT * FROM ' . self::$prefix . 'group_posts WHERE group_id = :gid ORDER BY date DESC LIMIT 1');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetch();
  }
  
  
  /**
   * Remove post
   *
   */
  public function removePost($postID) {
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'group_posts WHERE post_id = :pid');
    $removeQuery->bindParam(':pid', $postID, PDO::PARAM_INT);
    $removeQuery->execute();
  }
  
  /**
   * Add/update presence
   *
   */
  public function setPresence($groupID, $date, $userPresence) {
    foreach($userPresence as $userID => $presence) {
      $insertQuery = self::$connection->prepare('IF EXISTS (SELECT * FROM ' . self::$prefix . 'presence WHERE group_id = :gid AND date = :date AND user_id = :uid) THEN BEGIN UPDATE ' . self::$prefix . 'presence p SET presence = :present WHERE group_id = :gid AND date = :date AND user_id = :uid END ELSE BEGIN INSERT INTO p (group_id, date, user_id, presence) VALUES(:gid, :date, :uid, :present) END');
      $insertQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
      $insertQuery->bindParam(':date', $date, PDO::PARAM_STR, 10);
      $insertQuery->bindParam(':uid', $userID, PDO::PARAM_INT);
      $insertQuery->bindParam(':present', $presence, PDO::PARAM_INT);
      $insertQuery->execute();
    }
  }
  
  /**
   * @return array of days of workshops in group
   */
  public function getDays($groupID) {
    $selectQuery = self::$connection->prepare('SELECT DISTINCT days FROM ' . self::$prefix . 'presence WHERE group_id = :gid');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * @return presence of a particular day
   */
  public function getPresence($groupID, $date) {
    $selectQuery = self::$connection->prepare('SELECT user_id, presence FROM ' . self::$prefix . 'presence WHERE group_id = :gid AND date = :date');
    $selectQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $selectQuery->bindParam(':date', $date, PDO::PARAM_STR, 10);
    $selectQuery->execute();
    
    return $selectQuery->fetchAll();
  }
  
  
  /**
   * Removes presence from particular date
   *
   */
  public function removeDay($groupID, $date) {
    $removeQuery = self::$connection->prepare('DELETE FROM ' . self::$prefix . 'presence WHERE group_id = :gid AND date = :date');
    $removeQuery->bindParam(':gid', $groupID, PDO::PARAM_INT);
    $removeQuery->bindParam(':date', $date, PDO::PARAM_STR, 10);
    $removeQuery->execute();
  }
}