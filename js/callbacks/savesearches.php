<?php
/**
 * This script is the AJAX callback that saves a user's search
 */

define('IN_FS', true);

require_once('../../header.php');

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();

    if( !Post::has('csrftoken') ){
        header(':', true, 428); # 'Precondition Required'
        die('missingtoken');
    }elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
        # empty
    }else{
        header(':', true, 412); # 'Precondition Failed'
        die('wrongtoken');
    }
    
    $user->save_search();
}

?>
