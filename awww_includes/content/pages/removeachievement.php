<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$achievementID  = $_GET['id'];
$achievement    = $userdata->getAchievement($achievementID);
$user           = Session::getUser();
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Usuwanie osiągnięcia</h1>

<?php
if (!($user->isPrivileged() && $userdata->isInGroup($user->getID(), $achievement['group_id'])) && !$user->isAdmin()) { 
?>
  
  <h2 class="ginfo">Nie można usunąć, brak uprawnień</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>

<?php } else {
  $userdata->removeAchievement($achievementID);

?>
  
  <h2 class="ginfo">Usunięto osiągnięcie <?php echo $achievement['title']; ?></h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>
  
<?php
} 
?>