<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$id            = $_GET['uid'];
$group_id      = $_GET['gid'];

if (Session::getUser()->isPrivileged() && $userdata->isInGroup(Session::getUser()->getID(), $group_id) || Session::getUser()->isAdmin()) {
  $userdata->removeFromGroup($id, $group_id, false);
}
?>