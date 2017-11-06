<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$group_id      = $_GET['gid'];
$date          = $_GET['day'];

if (Session::getUser()->isPrivileged() && $userdata->isInGroup(Session::getUser()->getID(), $group_id) || Session::getUser()->isAdmin()) {
  $userdata->removeDay($group_id, $date);
}
?>