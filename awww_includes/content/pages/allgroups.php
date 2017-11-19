<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();
$userID = Session::getUser()->getID();
$groups = $userdata->getAllGroups();

$group_count = count($groups);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Wszystkie grupy</h1>

<?php

if(!Session::getUser()->isAdmin()) {
  ?>

  <h2 class="ginfo">Brak uprawnień</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>

<?php  
  die();
  return;
}
?>

<section class="row">
  <?php
  
  // display available groups
  foreach ($groups as $group) {
  ?>
  
  <div class="col-12 col-group">
    <div class="group-title">
      <h5><?php echo $group['group_name']; ?></h5>
      <a class="signin-link no-refresh" data-ref="group?id=<?php echo $group['group_id']; ?>" href="#">Otwórz grupę</a>
    </div>
    <div class="group-vacancies">Wolne miejsca: <?php echo $group['vacancies']; ?></div>
    <div class="group-desc"><?php echo $group['group_desc']; ?></div>
  </div>
  
  <?php
  }
  
  
  // if there is nothing to show
  if ($group_count == 0) { ?>
  
  <div class="col-12">
    <h2 class="ginfo">Obecnie nie żadnych grup</h2>
  </div>
  
  <?php } ?>
</section>