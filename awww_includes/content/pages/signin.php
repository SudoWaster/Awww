<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$mail          = Session::getUser()->getMail();
$group_id      = $_GET['g'];
$updateVacancy = !Session::getUser()->isPrivileged();

$result = $userdata->addToGroup($mail, $group_id, $updateVacancy);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Dodawanie do grupy</h1>

<?php
if($result) { 
  $group = $userdata->getGroup($group_id);
?>
  <h2 class="ginfo">Dodano do grupy <?php echo $group['group_name']; ?></h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>
<?php
} else {
?>
  <h2 class="ginfo">Dodawanie zakończone błędem</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>
<?php
}
?>