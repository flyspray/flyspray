<?php

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once '../../header.php';
global $proj, $fs;

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
  http_response_code(428); # 'Precondition Required'
  die('missingtoken');
}elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
  # empty
}else{
  http_response_code(412); # 'Precondition Failed'
  die('wrongtoken');
}
if (!$user->perms('is_admin')){
  http_response_code(403); # 'Forbidden'
  die(L('nopermission'));
}

$notify = new Notifications;
$result=$notify->sendEmail($user->infos['email_address'],'test','testcontent',1);

if($result !=1){
  http_response_code(406); # 'Not Acceptable'
}
echo 'ok';
?>
