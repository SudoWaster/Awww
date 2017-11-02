<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$userID       = Session::getUser()->getID();
$groupID      = $_GET['id'];
$group        = $userdata->getGroup($groupID);
$members      = $userdata->getAllFromGroup($groupID);
$instructors  = $userdata->getAllFromGroup($groupID, true);
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
<section class="row">

  <div class="col-12">
    <ul class="options">
      <li>
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="instructors" href="#">Prowadzący</a>
      </li>

      <li>
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="" href="#">Osiągnięcia</a>
      </li>

      <li>
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="" href="#">Obecność</a>
      </li>

      <li>
        <a role="button" class="btn btn-outline-danger no-refresh-confirm" data-msg="Na pewno chcesz opuścić grupę?" data-ref="leave?g=<?php echo $group['group_id']; ?>" href="#">Opuść grupę</a>
      </li>
    </ul>
  </div>

  <div class="col-12">
    <div class="group-desc"><?php echo $group['group_desc']; ?></div>
  </div>


  <div id="instructors" class="col-12 separated">
    <h5>Prowadzący</h5>
    
    <ul class="nav">
      <?php

      // display user groups
      foreach ($instructors as $user) {
      ?>

      <li class="nav-item">
        <a class="nav-link no-refresh" data-ref="user?id=<?php echo $user->getID(); ?>" href="#"><?php echo $user->getName(); ?></a>
      </li>

      <?php
      }
      ?>
    </ul>
  </div>
</section>