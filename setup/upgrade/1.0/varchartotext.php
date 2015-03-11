<?php
if ($conf['database']['dbtype'] == 'pgsql') {
    $db->Query('ALTER TABLE {prefs} ALTER COLUMN pref_value TYPE text');
    $db->Query('ALTER TABLE {prefs} ALTER COLUMN pref_value SET DEFAULT \'\'');    
}
else {
    $db->Query('ALTER TABLE {prefs} CHANGE `pref_value` `pref_value` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');
}
?>