<?php
final class Session {
  
  public static $sessionExpiration  = 5 * 60;   // 5 minutes expiration
  public static $sessionCookie      = 'usid';
  
  private $user;

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
  private function __construct() {
    startSession();
  }
  
  /**
   * Checks validity of session and optionally creates one
   *
   */
  public static startSession() {
    session_start();
    
    if(!self::isSessionValid() || (isset($this->$user) && !$this->$user->isLogged()) {
      self::destroySession();
    }
    
    session_id(self::getSession());
    
    updateSession();
  }
  
  /**
   * @return current session validity
   */
  private static isSessionValid() {
    $safeSID = isset($_SESSION['SAFE_SID']);
    $expired = $_SESSION['ACTIVITY'] + self::$sessionExpiration > time();
    $validIP = $_SERVER['REMOTE_ADDR'] === $_SESSION['PREV_REMOTE_ADDR'];
    $validUA = $_SERVER['HTTP_USER_AGENT'] === $_SESSION['PREV_USER_AGENT'];
    
    return $safeSID && !$expired && $validIP && $validUA;
  }
  
  /**
   * Save new session info
   */
  private static updateSession() {
    $_SESSION['ACTIVITY'] = getTimestamp();
    $_SESSION['PREV_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['PREV_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
    
    $_SESSION['SAFE_SID'] = true;
  }
  
  /**
   * Get or generate session id
   *
   * @returns session id
   */
  private static getSession() {
    
    if(!isset($_COOKIE[self::$sessionCookie])) {
      self::destroySession();
      setCookie(self::$sessionCookie, session_id(), time() + self::$cookieExpiration);
    }
    
    return $_COOKIE[self::$sessionCookie];
  }
  
  /**
   * Properly destroy session
   *
   */
  public static destroySession() {
    session_destroy();
    setcookie(self::$sessionCookie, "", time() - 3600);
    $user = null;
    session_regenerate_id();
  }
  
  /**
   * Binds logged user
   *
   */
  public bindUser($user) {
    if(!$user->isLogged()) {
      destroySession();
      return;
    }
    
    $this->$user = $user;
  }
       
  /**
   * @return logged status
   *
   */
  public isLogged() {
    return ($user != null && $user->isLogged());
  }
       
       
  /**
   * @return logged user
   *
   */
  public getUser() {
    return $user;
  }
}