<?php
require_once 'User.php';

/**
 * Stores database credentials and handles
 * data exchange
 *
 */
final class UserData {
  
  private static $connection;
  
  private $server   = '';
  private $database = '';
  private $user     = '';
  private $pass     = '';
  
  /**
   * Private constructor for singleton
   *
   */
  private function __construct() {
    connect();
  }
  
  
  
  /**
   * Get singleton instance
   *
   * @return GameDevData
   */
  public static function Instance() {
    static $instance = null;
    
    if ($instance === null) {
      $instance = new GameDevData();
    }
    
    return $instance;
  }
  
  /**
   * Connect to the database
   *
   */
  private function connect() {
    self::$connection = new PDO('mysql:host=$this->$server;dbname=$this->$database', $user, $pass);
    
    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  
}