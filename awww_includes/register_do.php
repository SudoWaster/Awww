<?php
require_once __DIR__ . '/../awww_engine/Session.class.php';
require_once __DIR__ . '/../awww_engine/User.class.php';
require_once __DIR__ . '/../awww_engine/UserData.class.php';
require_once __DIR__ . '/../awww_engine/Config.class.php';

Session::startSession();
$userdata = UserData::Instance();
$conf = Config::Instance();

$mail    = $_POST['mail'];

if(Session::isLogged()) {
  Session::destroySession();
  echo "ok";
  return;
}

$mail2   = $_POST['mail2'];
$name    = $_POST['name'];
$plain   = $_POST['pass'];

if (!($mail == $mail2)) {
  echo "inputEmail2";
  return;
}

if ($userdata->getUser($mail)->canLogin()) {
  echo "inputEmail";
  return;
}

if (strlen($plain) < 8) {
  echo "inputPassword";
  return;
}

$userdata->createUser($mail, $plain, $name, User::$USER_TYPES['STUDENT']);

echo "ok";
?>