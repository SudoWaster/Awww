<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$id            = Session::getUser()->getID();
$group_id      = $_GET['g'];
$group         = $userdata->getGroup($group_id);
$updateVacancy = !Session::getUser()->isPrivileged();

$userdata->removeFromGroup($id, $group_id, $updateVacancy);
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Usunięto z grupy</h1>
<h2 class="ginfo">Opuszczono grupę <?php echo $group['group_name']; ?></h2>
<a role="button" class="btn btn-outline-primary" href="apanel">Powrót</a>