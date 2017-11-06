<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$userID       = Session::getUser()->getID();
$groupID      = $_GET['id'];
$group        = $userdata->getGroup($groupID);
$members      = $userdata->getAllFromGroup($groupID);
$instructors  = $userdata->getAllFromGroup($groupID, true);
$posts        = $userdata->getPosts($groupID);
$presence     = $userdata->getUserPresence($userID, $groupID);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<?php
// if user tries to be smart
if (!$userdata->isInGroup($userID, $groupID) && !Session::getUser()->isAdmin()) { ?>

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
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="posts" href="#">Wpisy prowadzących</a>
      </li>
      
      <li>
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="achievements" href="#">Osiągnięcia</a>
      </li>
      
      <?php
      if (Session::getUser()->isPrivileged()) {?>
      <li>
        <a role="button" class="btn btn-outline-primary scroll-to" data-ref="presence" href="#">Obecność</a>
      </li>
      
      <li>
        <a role="button" class="btn btn-outline-primary edit-group-button" href="#">Edytuj</a>
      </li>
      <?php } ?>

      <li>
        <a role="button" class="btn btn-outline-danger no-refresh-confirm" data-msg="Na pewno chcesz opuścić grupę?" data-ref="leave?g=<?php echo $group['group_id']; ?>" href="#">Opuść grupę</a>
      </li>
    </ul>
  </div>

  <div class="col-12 group-info">
    <?php
    if(!Session::getUser()->isPrivileged()) { ?>
    <div class="alert alert-light">Obecność: <span class="badge badge<?php echo UserData::getBadgeClass($presence); ?>"><?php echo round(100 * $presence); ?>%</span></div>
    <?php } ?>
    
    <div class="group-vacancies">Wolne miejsca: <?php echo $group['vacancies']; ?></div>
    <div class="group-desc"><?php echo $group['group_desc']; ?></div>

  </div>
  
  <div class="col-12 group-edit hidden">
    
    
    <form id="group-edit-form">
      
    <input type="hidden" id="gid" value="<?php echo $group['group_id']; ?>" />
           
      <div class="form-group row">
        <label for="gname" class="col-4 col-form-label">Nazwa:</label>
        <div class="col-8">
          <input type="text" class="form-control" id="gname" value="<?php echo $group['group_name']; ?>" />
        </div>
      </div>

      <div class="form-group row">
        <label for="gvac" class="col-4 col-form-label">Miejsca:</label>
        <div class="col-8">
          <input type="number" class="form-control-inline" id="gvac" value="<?php echo $group['vacancies']; ?>" />
        </div>
      </div>

      <div class="form-group row">
        <div class="col-12">
          <textarea class="form-control" id="gdesc"><?php echo $group['group_desc']; ?></textarea>
        </div>
      </div>

      <div>
        <div class="form-group col-12">
          <input type="submit" role="button" class="btn btn-outline-primary save-group" value="Zapisz" />
          <button class="btn btn-outline-danger no-refresh-confirm" data-msg="Czy na pewno chcesz usunąć grupę?" data-ref="removegroup?id=<?php echo $groupID; ?>">Usuń grupę</button>
        </div>
      </div>
    </form>
  </div>


  <div id="instructors" class="col-12 separated">
    <h5>Prowadzący</h5>
    
    <ul class="nav">
      <?php

      // display user groups
      foreach ($instructors as $user) {
      ?>

      <li class="nav-item">
        <a class="nav-link no-refresh" data-ref="user?id=<?php echo $user->getID(); ?>" href="#"><?php echo $user->getFullName(); ?></a>
      </li>

      <?php
      }
      ?>
    </ul>
  </div>
</section>







<!-- POSTS -->


<h2>Wpisy prowadzących</h2>
<?php
if (Session::getUser()->isPrivileged()) { ?>

<a role="button" class="btn btn-outline-primary new-button" href="">Dodaj</a>

<?php } ?>

<section id="posts" class="row posts">
  
  <?php
  if (Session::getUser()->isPrivileged()) {?>
    
  <div id="post-new" class="col-12 post">
    <div class="post-content-editable hidden">
      <form method="post">
        <input type="hidden" class="post-id" value="new" />
        <input type="hidden" class="group-id" value="<?php echo $groupID; ?>" />
        <textarea class="post-content-area" name="post-content"></textarea>
      </form>
    </div>
  </div>
  
  <?php } ?>
  
  <?php
  foreach ($posts as $post) { 
    $author = $userdata->getUserByID($post['op_id']);
  ?>
  <div id="post-<?php echo $post['post_id']; ?>" class="col-12 post">
    <div class="post-info">
      <span class="date"><?php echo $post['date']; ?></span>
      <span class="author"><?php echo $author->getName(); ?></span>
      
      <?php 
      if (Session::getUser()->isPrivileged()) { ?>
      <a role="button" class="btn btn-outline-primary edit-button" data-post="<?php echo $post['post_id']; ?>" href="#">Edytuj</a>
      <a rele="button" class="btn btn-outline-danger no-refresh-confirm" data-msg="Czy na pewno chcesz usunąć ten post?" data-ref="removepost?id=<?php echo $post['post_id']; ?>&gid=<?php echo $groupID; ?>" href="#">Usuń</a>
      <?php } ?>
      
    </div>
    
    <div class="post-content">
      <?php echo $post['post_content']; ?>
    </div>
    
    <?php
    if (Session::getUser()->isPrivileged()) {
    ?>
    <div class="post-content-editable hidden">
      <form method="post">
        <input type="hidden" class="post-id" value="<?php echo $post['post_id']; ?>" />
        <input type="hidden" class="group-id" value="<?php echo $groupID; ?>" />
        <textarea class="post-content-area" name="post-content"><?php echo $post['post_content']; ?></textarea>
      </form>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
</section>





<!-- PRESENCE -->

<?php
if (Session::getUser()->isPrivileged()) { 
  $days = $userdata->getDays($groupID);
  $group_members = $userdata->getAllFromGroup($groupID, false);
?>
<section id="presence" class="row">
  <div class="col-12 separated bg-light">
    <h2>Obecność</h2>
    
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Użytkownik</th>
            <th>Osiągnięcia</th>
            <th>Obecność</th>
            <th>Opcje</th>
            <th>Dzisiaj</th>
            <?php foreach ($days as $day) { ?>
            <th><?php echo $day['date']; ?> <a class="btn btn-outline-danger btn-sm no-refresh-confirm-reload" data-msg="Wykasować obecność dla dnia <?php echo $day['date']; ?>?" data-ref="delday?gid=<?php echo $groupID; ?>&day=<?php echo $day['date']; ?>" href="#">X</a></th>
            <?php } ?>
          </tr>
        </thead>
        
        <tbody>
        <?php 
        foreach ($group_members as $member) {
          $member_presence  = $userdata->getUserPresence($member->getID(), $groupID);
          $member_progress  = $userdata->getUserProgress($member->getID(), $groupID);

        ?>
          <tr>
            <td>
              <a class="no-refresh" data-ref="user?id=<?php echo $member->getID(); ?>" href="#"><?php echo $member->getFullName(); ?></a> 
            </td>

            <td>
              <span class="badge badge-<?php echo UserData::getAlertClass($member_progress); ?>"><?php echo round(100 * $member_progress); ?>%</span>
            </td>
            
            <td>
              <span class="badge badge-<?php echo UserData::getBadgeClass($member_presence); ?>"><?php echo round(100 * $member_presence); ?>%</span> 
            </td>

            <td>
              <a class="btn btn-outline-danger btn-sm no-refresh-confirm-reload" data-msg="Czy na pewno chcesz usunąć <?php echo $member->getFullName(); ?> z grupy?" data-ref="kick?uid=<?php echo $member->getID(); ?>&gid=<?php echo $groupID; ?>" href="#">Usuń z grupy</a>
            </td>
            
            <td>
              <input type="checkbox" class="presence-check" data-date="<?php echo date('o-m-d'); ?>" data-group="<?php echo $groupID; ?>" data-user="<?php echo $member->getID(); ?>" <?php if (date('o-m-d') == $days[0]['date']) echo 'disabled'; ?>/> 
            </td>
            
            <?php 
            foreach ($days as $day) { ?>
            
            <td>
              <input type="checkbox" class="presence-check" data-date="<?php echo $day['date']; ?>" data-group="<?php echo $groupID; ?>" data-user="<?php echo $member->getID(); ?>" <?php echo $userdata->wasPresent($member->getID(), $groupID, $day['date']) ? 'checked' : ''; ?> />
            </td>
            
            <?php } ?>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

</section>
<?php } ?>






<!-- ACHIEVEMENTS -->


<h2>Osiągnięcia</h2>

<?php 
if (Session::getUser()->isPrivileged()) {?>

<a role="button" class="btn btn-outline-primary add-achievement-button" href="#">Dodaj</a> 

<div class="new-achievement hidden">
  <form id="achievement-add-form" method="post">
    <input type="hidden" class="group-id" value="<?php echo $groupID; ?>" />
    <input type="text" class="form-control achievement-title" />
    <textarea class="form-control achievement-desc"></textarea>
    <input type="submit" class="btn btn-outline-primary" value="Zapisz" />
  </form>
</div>

<?php } ?>

<section id="achievements">

<?php
$achievements = $userdata->getGroupAchievements($groupID);
$userBadges   = array();
if(!Session::getUser()->isPrivileged()) {
  $userBadges = $userdata->getUserAchievements($userID, $groupID);
}

$i = 0;

foreach ($achievements as $achievement) {
  $i++;
  
  if ($i == 1) { ?> <div class="row"> <?php } 
  
  $alertClass = in_array ($achievement['achievement_id'], array_column($userBadges, 'achievement_id')) ? 'alert-success' : 'alert-light';
  ?>
  
    <div class="col-12 col-md-4">
      <div class="alert <?php echo $alertClass; ?>">
        <h5><?php echo $achievement['title']; ?></h5>
        <p><?php echo $achievement['description']; ?></p>
        <?php 
        if (Session::getUser()->isPrivileged()) {?>

        <a role="button" class="btn btn-outline-primary no-refresh" data-ref="achievementassign?id=<?php echo $achievement['achievement_id']; ?>&gid=<?php echo $groupID; ?>" href="#">Edytuj przydział</a> 
        <a role="button" class="btn btn-outline-danger no-refresh-confirm" data-msg="Czy na pewno chcesz usunąć to osiągnięcie?" data-ref="removeachievement?id=<?php echo $achievement['achievement_id']; ?>" href="#">Usuń</a> 

        <?php } ?>
      </div>
    </div>
  
  <?php
  if ($i == 3) { $i = 0; ?> </div> <?php }
}
  
if ($i != 0) { ?> </div> <?php } ?>
  

</section>

<script src="js/tinymce/tinymce.min.js"></script>
<script src="js/groups.jquery.js"></script>