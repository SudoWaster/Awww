<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$user   = Session::getUser();
$groups = $userdata->getUserGroups($user->getID());
?>
<link rel="stylesheet" type="text/css" href="css/overview.css" />
<h1>Twoje grupy</h1>

<?php
$i = 0;
foreach ($groups as $group) {
  $i++;
  
  $progress = $userdata->getUserProgress($user->getID(), $group['group_id']);
  
  if($i == 1) {?> <div class="row"> <?php } ?>
  
  <div class="col-12 col-md-4 overview-group-block">
    
    <?php if(!$user->isPrivileged()) { ?>
    <div class="user-progress">
      <div class="alert <?php echo UserData::getAlertClass($progress); ?>">
        <?php echo round(100 * $progress); ?>%
      </div>
    </div>
    <?php } ?>
    
    <div class="overview-group-name">
      <a role="button" class="btn btn-outline-primary no-refresh" data-ref="group?id=<?php echo $group['group_id']; ?>" href="#"><?php echo $group['group_name']; ?></a>
    </div>
  </div>
  
  <?php if($i == 3) { $i = 0; ?> </div> <?php }
}

if($i != 0) { ?> </div> <?php }
?>

<h1>Najnowsze wpisy z grup</h1>

<section class="row posts">
<?php
  foreach ($groups as $group) { 
    $post   = $userdata->getNewestPost($group['group_id']);
    
    if(!$post) {
      continue;
    }
    
    $author = $userdata->getUserByID($post['op_id']);
  ?>
  <div class="col-12 post">
    <h5><a role="button" class="no-refresh" data-ref="group?id=<?php echo $group['group_id']; ?>" href="#!group?id=<?php echo $group['group_id']; ?>"><?php echo $group['group_name']; ?></a></h5>
    <div class="post-info">
      <span class="date"><?php echo $post['date']; ?></span>
      <span class="author"><?php echo $author->getName(); ?></span>
    </div>
    
    <div class="post-content">
      <?php echo $post['post_content']; ?>
    </div>
  
  </div>
    
  <?php } ?>
 
</section>