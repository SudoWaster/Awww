<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../../awww_engine/Session.class.php';

Session::startSession();
$userdata = UserData::Instance();
$groups = $userdata->getAvailableGroups(Session::getUser()->getID());
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Dostępne grupy</h1>
<section class="row">
  <?php
  foreach ($groups as $group) {
  ?>
  <div class="col-12 col-group">
    <div class="group-title">
      <h5><?php echo $group['group_name']; ?></h5>
      <a class="signin-link no-refresh" data-ref="signin&g=<?php echo $group['group_id']; ?>" href="#">Zapisz się</a>
    </div>
    <span class="group-desc"><?php echo $group['group_desc']; ?></span>
  </div>
  <?php
  }
  
  if (count($groups) == 0) {
  ?>
  <div class="col-12">
    <h2 class="ginfo">Obecnie nie ma wolnych miejsc w grupach</h2>
  </div>
  <?php
  }
  ?>
</section>