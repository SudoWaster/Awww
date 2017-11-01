<?php
require_once __DIR__ . '/../awww_engine/Session.class.php';
require_once __DIR__ . '/../awww_engine/UserData.class.php';
require_once __DIR__ . '/../awww_engine/Config.class.php';

Session::startSession();
$userdata = UserData::Instance();
$conf = Config::Instance();

if(Session::isLogged()) {
  Session::destroySession();
  header('location: ' . $conf->get()['HOST']['ADDRESS'] . 'awww-login.php?logout');
}

$mail = $_POST['login'];
$pass = $_POST['pass'];

$user = $userdata->getSignedIn($mail, $pass);

Session::bindUser($user);
?>