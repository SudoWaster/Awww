<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();
$userID = Session::getUser()->getID();
$groupID = $_GET['id'];
$group = $userdata->getGroup($groupID);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<?php
// if user tries to be smart
if (!$userdata->isInGroup($userID, $groupID)) { ?>

  <h1>Brak dostępu</h1>
  <h2 class="ginfo">Nie należysz do grupy <?php echo $group['group_name']; ?></h2>

<?php
  return;
}
?>

<h1>Grupa <?php echo $group['group_name']; ?></h1>
<ul class="options">
  <li>
    <a role="button" class="btn btn-outline-primary no-refresh" data-ref="" href="#">Prowadzący zajęcia</a>
  </li>
  
  <li>
    <a role="button" class="btn btn-outline-primary no-refresh" data-ref="" href="#">Osiągnięcia</a>
  </li>
  
  <li>
    <a role="button" class="btn btn-outline-danger no-refresh-confirm" data-msg="Na pewno chcesz opuścić grupę?" data-ref="leave?g=<?php echo $group['group_id']; ?>" href="#">Opuść grupę</a>
  </li>
</ul>

<div class="group-desc"><?php echo $group['group_desc']; ?></div>