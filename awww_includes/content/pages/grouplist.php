<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();
$userID = Session::getUser()->getID();
$groups = $userdata->getUserGroups($userID);

$group_count = count($groups);
?>