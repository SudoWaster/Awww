<?php
final class Config {
  private static $configFile;
  
  private $config;
  
  /**
   * Get singleton instance
   *
   * @return Session
   */
  public static function Instance() {
    static $instance = null;
    
    if ($instance === null) {
      $instance = new Config();
    }
    
    return $instance;
  }
  
  
  /**
   * Private constructor for singleton
   *
   */
  private function __construct() {
    self::$configFile = __DIR__ . '/awwwconfig.ini';
    
    $this->config = parse_ini_file(self::$configFile, true);
  }
  
  
  /**
   * @return config
   *
   */
  public function get() {
    return $this->config;
  }
}