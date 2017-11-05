<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$user           = Session::getUser();
$groupName      = $_POST['name'];
$groupDesc      = $_POST['desc'];
$groupVacancies = $_POST['vacancies'];

if (!Session::getUser()->isPrivileged()) {
  return;
}

if (isset($_POST['id'])) {
  $userdata->updateGroup($_POST['id'], $groupName, $groupDesc, $groupVacancies);

} else {
  $gid = $userdata->addGroup($groupName, $groupDesc, $groupVacancies);
  
  echo $gid;
  
  if(!$gid) { 
    return;
  }
  
  $userdata->addToGroup(Session::getUser()->getMail(), $gid, false);
}
?>