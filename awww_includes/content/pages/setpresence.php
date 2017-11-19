<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$group_id      = $_POST['gid'];
$date          = $_POST['day'];
$user_id       = $_POST['uid'];
$present       = $_POST['present'];

if ((Session::getUser()->isPrivileged() && $userdata->isInGroup(Session::getUser()->getID(), $group_id)) || Session::getUser()->isAdmin()) {
  $userdata->setPresence($user_id, $group_id, $date, $present);
}
?>