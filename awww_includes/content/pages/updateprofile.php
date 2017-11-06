<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$user         = Session::getUser();

$name   = $_POST['name'];
$lname  = $_POST['lname'];
$mail   = $_POST['mail'];
$npass  = $_POST['npass'];
$pass   = $_POST['pass'];

if ($npass == NULL || strlen($npass) <= 0) {
  $npass = $pass;
}

if ($userdata->getSignedIn(Session::getUser()->getMail(), $pass)->getID() != Session::getUser()->getID()) {
  die();
  return;
}

$userdata->updateUser(Session::getUser()->getID(), $mail, $npass, $name, $lname);

$user = $userdata->getSignedIn($mail, $npass);
Session::bindUser($user);

if ($npass != $pass) {
  Session::destroySession();
}
?>