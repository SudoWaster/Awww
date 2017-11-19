<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$achiev_id     = $_POST['aid'];
$user_id       = $_POST['uid'];
$assigned      = $_POST['assigned'];

if (Session::getUser()->isPrivileged() || Session::getUser()->isAdmin()) {
  if($assigned) {
    $userdata->assignAchievement($achiev_id, $user_id);  
  } else {
    $userdata->rejectAchievement($achiev_id, $user_id); 
  }
  
}
?>