<?php
chdir('..');
$hide='_'.md5(rand().time()).'_setup';
rename('setup', $hide); # Hide the setup dir with a random name, not deleting it. Just in case to have the complete software together or something got wrong.
chmod($hide, 0400); # make it extra unaccessible (directory x bit) just in case the dir lands on search engines index..
echo '<a href="../">You can now use Flyspray.</a>';
?>
