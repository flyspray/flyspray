<?php
# no caching to prevent old pages if user goes back and forth during install
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

# Step 1 and 2 now working also with SAFE_MODE enabled, so currently no need for this step to try with perl as fallback. But just in case...
#if(ini_get('safe_mode') == 1){
#	echo '<h3>PHP safe_mode is enabled. We can\'t install composer by PHP itself from webbrowser.</h3><h3>But lets test if we can workaround it with Perl:</h3>';
#	echo '<a href="./setup/composerit.pl">Test using Perl:  composerit.pl</a>';
#} else{
	echo '<h3>Step 1: Trying to download Composer:</h3>';
	#chdir('..');

	# This works also in php5.3.* with SAFE_MODE enabled
	$composerfile= file_get_contents('https://getcomposer.org/installer');
	file_put_contents('composerinstaller', $composerfile);

	echo 'Download done'.'<br>';
	echo '<h3>Step 2: Trying to load composerinstaller into the running php script</h3>';

	# Now lets execute it directly by loading the file composerinstaller. :-)
	# XXX lol PHP5.3.* with SAFE_MODE enabled forces us to be 'unsafe' ...
	if(ini_get('safe_mode') == 1){
		#$argv=array(); # just for avoiding warnings
		$argv=array('--disable-tls'); # just for avoiding warnings
	}else{
		$argv=array();
		putenv('COMPOSER_HOME=.'); # fake env var; aww not working in SAFE_MODE ! Do we need to automatic patch composerinstaller for running without HOME or COMPOSER_HOME?
	}
	echo '<p>Wait a few seconds until composerinstaller put his output under the button. If looking good go to step 3.</p>';
	echo '<a href="composerit2.php" class="button"><h3>Go To Step 3: Trying to install dependencies</h3></a>';
	echo '<pre>';
	require 'composerinstaller';
	# well, composerinstaller exits itself, so no more code needed here..
	echo '</pre>';

# not executed after require ...
/*
	$cmd2 = 'php composer.phar install';
	echo $cmd2.'<br/><br/>';
	shell_exec($cmd2);
	echo '<strong>Done</strong>';

	echo '<h3>Step 3: Checking and cleaning:</h3>';
	if (is_readable('./vendor/autoload.php')){
		echo 'Composer installation ok<br /><br />';
	} else{
		echo 'Composer installation failed<br /><br />';
	}
	echo '<a href="./index.php">Go back</a>';
*/
#}
?>
