<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$userID       = $_GET['id'];
$user         = $userdata->getUserByID($userID);
$groups       = $userdata->getUserGroups($userID);
$achievements = $userdata->getUserAchievements($userID);

?>

<h1><?php echo $user->getFullName(); ?></h1>

<div class="row">
  <div class="col-4 col-md-2 item-name">
    Typ
  </div>
  
  <div class="col-8 col-md-10">
    <?php echo $user->getTypeDesc(); ?>
  </div>
</div>

<div class="row">
  <div class="col-4 col-md-2 item-name">
    E-mail
  </div>
  
  <div class="col-8 col-md-10">
    <a href="mailto:<?php echo $user->getMail(); ?>"><?php echo $user->getMail(); ?></a>
  </div>
</div>

<div class="bg-light profile-section row">
  <div class="col-12">
    <h5>Grupy</h5>
  </div>
  
  <div class="col-12">
    <ul class="group-list">
    <?php 
    foreach ($groups as $group) { 
      $progress = $userdata->getUserProgress($userID, $group['group_id']);
    ?>

      <li>
        <div class="row">
          
          <?php if (!$user->isPrivileged()) { ?>
          
          <div class="col-12 col-md-3 user-progress">
            <div class="alert <?php echo UserData::getAlertClass($progress); ?>">
              <?php echo round(100 * $progress); ?>%
            </div>
          </div>
          
          <?php } ?>
          
          <div class="col-12 col-md-9">
            <?php echo $group['group_name']; ?>
            <p class="group-desc"><?php echo $group['group_desc']; ?></p>
          </div>
        </div>
      </li>

    <?php } ?>
    </ul> 
  </div>
</div>

<div class="row">


</div>