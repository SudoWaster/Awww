<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();
$groupID  = $_GET['id'];
$group    = $userdata->getGroup($groupID);
$user     = Session::getUser();
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Usuwanie grupy</h1>

<?php
if (!($user->isPrivileged() && $userdata->isInGroup($user->getID(), $groupID)) && !$user->isAdmin()) { 
?>
  
  <h2 class="ginfo">Nie można usunąć, brak uprawnień</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>

<?php } else {
  $userdata->removeGroup($groupID);

?>
  
  <h2 class="ginfo">Usunięto grupę <?php echo $group['group_name']; ?></h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>
  
<?php
} 
?>