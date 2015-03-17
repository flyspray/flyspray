<?php

# untested!
if(ini_get('safe_mode') == 1){
	echo '<h3>PHP safe_mode is enabled. We can\'t install composer by PHP itself from webbrowser.</h3><h3>But lets test if we can workaround it with Perl:</h3>';
	echo '<a href="./setup/composerit.pl">Test using Perl:  composerit.pl</a>';
} else{
	// Test working, Psycho
	echo '<h3>Step 1: Trying to download Composer:</h3>';
	chdir('../');
	$cmd = 'php -r "readfile(\'https://getcomposer.org/installer\');" | php';
	echo $cmd.'<br/><br/>';
	shell_exec($cmd);
	echo '<strong>Done</strong>';

	echo '<h3>Step 2: Trying to install dependencies:</h3>';
	$cmd2 = 'php composer.phar install';
	echo $cmd2.'<br/><br/>';
	shell_exec($cmd2);
	echo '<strong>Done</strong>';

	echo '<h3>Step 3: Checking and cleaning:</h3>';
	if (is_readable('./vendor/autoload.php')){
		echo 'Composer installation ok<br /><br />';
	}
	else{
		echo 'Composer installation failed<br /><br />';
	}

	echo '<a href="./index.php">Go back</a>';
}

?>
