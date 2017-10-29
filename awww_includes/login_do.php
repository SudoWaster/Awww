<?php
require_once __DIR__ . '/../awww_engine/Session.class.php';
require_once __DIR__ . '/../awww_engine/UserData.class.php';

Session::startSession();
$userdata = UserData::Instance();

$mail = $_POST['login'];
$pass = $_POST['pass'];

$user = $userdata->getSignedIn($mail, $pass);

Session::bindUser($user);
?>