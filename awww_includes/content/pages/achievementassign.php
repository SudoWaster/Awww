<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$userID       = Session::getUser()->getID();
$achievID     = $_GET['id'];
$groupID      = $_GET['gid'];
$group        = $userdata->getGroup($groupID);
$achievement  = $userdata->getAchievement($achievID);
$members      = $userdata->getAllFromGroup($groupID);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Przypisywanie achievementa</h1>

<div class="alert">
  
  <h5><?php echo $achievement['title']; ?></h5>
  <p><?php echo $achievement['description']; ?></p>
  
</div>

<?php

if (!((Session::getUser()->isPrivileged() && $userdata->isInGroup($userID, $groupID)) || Session::getUser()->isAdmin())) {
?>

  <h2 class="ginfo">Brak uprawnień</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>

<?php
  return;
}

$group_members = $userdata->getAllFromGroup($groupID, false);
?>
<section id="achievements" class="row">
  
<?php
foreach ($group_members as $member) {
  $member_progress  = $userdata->getUserProgress($member->getID(), $groupID);
  $userBadges = $userdata->getUserAchievements($member->getID(), $groupID);
?>

  <div class="col-12">
    <div class="input-group">
      <input type="text" readonly value="<?php echo $member->getFullName(); ?>">
      <span class="input-group-addon">
        <input type="checkbox" class="achievement-check" data-user="<?php echo $member->getID(); ?>" data-achiev="<?php echo $achievID; ?>" <?php
  if(in_array ($achievID, array_column($userBadges, 'achievement_id'))) {
    echo 'checked';
  } ?>>
      </span>
    </div>
  </div>
  
  <?php } ?>
  
  
  <div class="col-12">
    <a role="button" class="btn btn-outline-primary no-refresh" data-ref="group?id=<?php echo $groupID; ?>" href="#">Powrót</a>
  </div>
</section>


<script src="js/groups.jquery.js"></script>