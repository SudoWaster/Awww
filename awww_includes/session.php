<?php
require_once __DIR__ . '/../awww_engine/Session.class.php';
require_once __DIR__ . '/../awww_engine/Config.class.php';

Session::startSession();
$conf = Config::Instance();

if(!Session::isLogged()) {
  header('location: ' . $conf->get()['HOST']['ADDRESS'] . 'awww-login.php?error');
}


if(isset($PRIVILEGED) && $PRIVILEGED) {
  if(!Session::getUser()->isPrivileged()) {
    header('location: ' . $conf->get()['HOST']['ADDRESS'] . 'awww-panel.php');
  }
}

?>