<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$user     = Session::getUser();
$groupID  = $_GET['gid'];
$postID   = $_GET['id'];
$post     = $userdata->getPost($postID);
?>
  
<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Usuwanie posta</h1>
  
<?php
if (!($user->isPrivileged() && $userdata->isInGroup($user->getID(), $groupID)) && !$user->isAdmin()) { 
?>
  <h2 class="ginfo">Nie można usunąć, brak uprawnień</h2>
  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>
<?php } else { 
$userdata->removePost($postID);
?>
  <h2 class="ginfo">Usunięto poniższy post!</h2>

  <div class="post">
    
    <div class="post-info">
      <span class="date"><?php echo $post['date']; ?></span>
      <span class="author"><?php echo $user->getName(); ?></span>
    </div>
    
    <div class="post-content"><?php echo $post['post_content']; ?></div>
  </div>

  <a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>

<?php } ?>