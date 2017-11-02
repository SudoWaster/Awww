<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();
$userID = Session::getUser()->getID();
$groups = $userdata->getAllGroups();

$group_count = count($groups);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Dostępne grupy</h1>
<section class="row">
  <?php
  
  // display available groups
  foreach ($groups as $group) {
    
    // if user already in group, skip
    if($userdata->isInGroup($userID, $group['group_id'])) {
      $group_count--;
      continue;
    }
  ?>
  
  <div class="col-12 col-group">
    <div class="group-title">
      <h5><?php echo $group['group_name']; ?></h5>
      <a class="signin-link no-refresh" data-ref="signin?g=<?php echo $group['group_id']; ?>" href="#">Zapisz się</a>
    </div>
    <span class="group-desc"><?php echo $group['group_desc']; ?></span>
  </div>
  
  <?php
  }
  
  
  // if there is nothing to show
  if ($group_count == 0) { ?>
  
  <div class="col-12">
    <h2 class="ginfo">Obecnie nie ma zapisów do żadnej grupy</h2>
  </div>
  
  <?php } ?>
</section>