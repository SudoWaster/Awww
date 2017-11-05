<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$user   = Session::getUser();
$groups = $userdata->getUserGroups($user->getID());
?>
<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Najnowsze informacje z grup</h1>

<section class="row">
<?php
  foreach ($groups as $group) { 
    $post   = $userdata->getNewestPost($group['group_id']);
    
    if(!$post) {
      continue;
    }
    
    $author = $userdata->getUserByID($post['op_id']);
  ?>
  <div class="col-12">
    <h5><?php echo $group['group_name']; ?></h5>
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