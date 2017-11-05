<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$user           = Session::getUser();
$groupID        = $_POST['gid'];
$aName          = $_POST['title'];
$aDesc          = $_POST['desc'];

if (!Session::getUser()->isPrivileged()) {
  return;
}

$userdata->addAchievement($aName, $aDesc, $groupID);
?>