<?php
require_once __DIR__ . '/../awww_engine/Session.class.php';
require_once __DIR__ . '/../awww_engine/UserData.class.php';

Session::startSession();
$userdata = UserData::Instance();

if(!Session::isLogged()) {
  $conf = parse_ini_file(__DIR__ . '../awww_engine/awwwconfig.ini', true);
  header('location: ' . $conf['PAGE']['ADDRESS'] . 'awww-login.php?error');
}

?>