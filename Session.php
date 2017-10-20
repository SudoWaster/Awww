<?php

class Session {
  
  public static $sessionExpiration  = 5 * 60;   // 5 minutes expiration
  public static $sessionCookie      = 'usid';
  
  private static $hashSaltPhrase  = 'AwW54l7';  // DO NOT CHANGE UNLESS YOU WANT TO
  private static $hashSaltMod     = 7;          // RESET PASSWORDS FOR EVERYONE

  
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
   * Checks validity of session and optionally creates one
   *
   */
  private function __construct() {
    session_start();
    
    if(!self::isSessionValid()) {
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
    session_regenerate_id();
  }
  
  /**
   * @return salt-hashed string
   */
  public static salthash($string, $requestUser) {
    
    $startPos = strlen($string) / 2 + strlen(self::$hashSaltMod) - 1;
    
    if($startPos >= strlen($string) % self::$hashSaltMod) {
      $startPos = - (strlen($string) % self::$hashSaltMod);
    }
    
    $newstr = substr_replace($string, self::$hashSaltPhrase, $startPos, 0);
    
    $startPos = (strlen($string) * strlen($requestUser)) % strlen($newstr);
    $newstr = substr_replace($newstr, $requestUser, $startPos, 0);
    
    return hash('sha256', $newstr);
  }
}