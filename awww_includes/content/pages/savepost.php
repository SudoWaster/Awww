<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata = UserData::Instance();

$post_id  = $_POST['id'];
$post     = $_POST['post'];
$group_id = $_POST['gid'];
$op_mail  = Session::getUser()->getMail();

if($post_id == 'new') {
  $userdata->addPost($op_mail, $group_id, $post);
} else {
  $userdata->updatePost($op_mail, $group_id, $post_id, $post);
}
?>