<?php

# untested!
if(ini_get('safe_mode') == 1){
        echo '<h3>PHP safe_mode is enabled. We can\'t install composer by PHP itself from webbrowser.</h3><h3>But lets test if we can workaround it with Perl:</h3>';
        echo '<a href="composerit.pl">Test using Perl:  composerit.pl</a>';
} else{
        echo "<h3>Trying to do composer stuff:</h3>";
        $cmd ='curl -sS https://getcomposer.org/installer | php';
        shell_exec( $cmd );
        echo 'Step 1 done';

        $cmd2 ='php composer.phar install';
        echo '<h3>Step 2: '.$cmd2.'</h3>';
        shell_exec( $cmd2 );
        echo 'Step 2 done';
}

?>
