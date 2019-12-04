<?php
define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

$webdir = dirname(dirname(dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'utf-8'))));
require_once '../../header.php';

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
} else {
    $user = new User(0, $proj);
}

# TODO csrftoken checking
# peterdd: Well, not sure if we need anti CSRF token check here.
# But whenever a user looses their session when editing a task or comment over a loooong time
# (longer than session timeout on server),
# then a preview error message from the preview check would warn the user that something is wrong.
# And if user is smart will copy their text written over a loooong text to their clipboard or local editor
# before pressing the final submit button to avoid frustration.
# (technics like autorefreshing sessions e.g. by polling for notifications as long as browser tab is open aside..)

$csp->add('img-src', "'self'");
$csp->emit();

if ($conf['general']['syntax_plugin'] == 'dokuwiki'){
	echo TextFormatter::render(Post::val('text'));
} else {

	# future stub: sanitization server side for the CKEditor Preview plugin
	# see https://ckeditor.com/docs/ckeditor4/latest/guide/dev_best_practices.html#validate-preview-content
	$purifierconfig = HTMLPurifier_Config::createDefault();
	if ($fs->prefs['relnofollow']) {
		$purifierconfig->set('HTML.Nofollow', true);
	}
	# should be in sync with settings used by the real save
	#$purifierconfig->set('AutoFormat.Linkify',true);
	
	$purifier = new HTMLPurifier($purifierconfig);
	echo $purifier->purify(Post::val('text'));
}

?>
