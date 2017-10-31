<?php
require_once __DIR__ . '/User.class.php';

final class Session {
  
  public static $sessionExpiration  = 5 * 60;   // 5 minutes expiration
  public static $sessionCookie      = 'usid';
  
  private static $user;

  /**
   * Get singleton instance
   *
   * @return Session
   */
  public static function Instance() {
    static $instance = null;
    
    if ($instance === null) {
      $instance = new Session();
    }
    
    return $instance;
  }
  
  
  /**
   * Private constructor for singleton
   *
   */
  private function __construct() {}
  
  /**
   * Checks validity of session and optionally creates one
   *
   */
  public static function startSession() {
    if(!isset($_SESSION)) {
      session_start();
    }
    
    if(!self::isSessionValid()) {
      self::destroySession();
    }
    
    self::updateSession();
  }
  
  /**
   * @return current session validity
   */
  private static function isSessionValid() {
    $safeSID    = isset($_SESSION['SAFE_SID']);
    $notExpired = isset($_SESSION['ACTIVITY']) 
      && ($_SESSION['ACTIVITY'] + self::$sessionExpiration >= time());
    $validIP    = isset($_SESSION['PREV_REMOTE_ADDR']) 
      && $_SERVER['REMOTE_ADDR'] == $_SESSION['PREV_REMOTE_ADDR'];
    $validUA    = isset($_SESSION['PREV_USER_AGENT'])
      && $_SERVER['HTTP_USER_AGENT'] == $_SESSION['PREV_USER_AGENT'];
    
    return $safeSID && $notExpired && $validIP && $validUA;
  }
  
  /**
   * Save new session info
   */
  private static function updateSession() {
    $_SESSION['ACTIVITY'] = time();
    $_SESSION['PREV_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['PREV_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    
    $_SESSION['SAFE_SID'] = true;
    
    if(isset($_SESSION['CURRENT_USER'])) {
      $user = unserialize($_SESSION['CURRENT_USER']);
      self::bindUser($user);
    }
  }
  
  /**
   * Properly destroy session
   *
   */
  public static function destroySession() {
    $_SESSION = array();
    $user = null;
    session_regenerate_id();
  }
  
  /**
   * Binds logged user
   */
  public static function bindUser($user) {
    if(!$user->canLogin()) {
      self::destroySession();
      return;
    }
    
    $_SESSION['CURRENT_USER'] = serialize($user);
    self::$user = $user;
  }
       
  /**
   * @return logged status
   */
  public static function isLogged() {
    return (isset(self::$user) && self::$user != null && self::$user->canLogin());
  }
       
       
  /**
   * @return logged user
   */
  public static function getUser() {
    return self::$user;
  }
}