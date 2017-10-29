<?php
require_once '../awww_engine/Session.class.php';
require_once '../awww_engine/UserData.class.php';

Session::startSession();
$userdata = UserData::Instance();

if(!Session::isLogged()) {
  $conf = parse_ini_file("awwwconfig.ini", true);
  header('location: ' . $conf['HOST']['ADDRESS'] . 'awww-login.php');
}

?>