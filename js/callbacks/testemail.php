<?php

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once('../../header.php');
global $proj, $fs;

$baseurl = dirname(dirname($baseurl)) .'/' ;

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
  $user = new User(Cookie::val('flyspray_userid'));
  $user->check_account_ok();
} else {
  $user = new User(0, $proj);
}

// don't allow anonymous users to access this page at all
if ($user->isAnon()) {
  die(L('nopermission'));
}
load_translations();

if( !Post::has('csrftoken') ){
  header(':', true, 428); # 'Precondition Required'
  die('missingtoken');
}elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
  # empty
}else{
  header(':', true, 412); # 'Precondition Failed'
  die('wrongtoken');
}
if (!$user->perms('is_admin')){
  header(':', true, 403); # 'Forbidden'
  die(L('nopermission'));
}

$notify = new Notifications;
$result=$notify->SendEmail($user->infos['email_address'],'test','testcontent',1);

if($result !=1){
  header(':', true, 406); # 'not acceptable'
}
echo 'ok';
?>
