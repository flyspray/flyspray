<?php
if ($conf['database']['dbtype'] == 'pgsql') {
    $db->query('ALTER TABLE {prefs} ALTER COLUMN pref_value TYPE text');
    $db->query('ALTER TABLE {prefs} ALTER COLUMN pref_value SET DEFAULT \'\'');    
}
elseif($db->dbtype=='mysqli' || $db->dbtype=='mysql') {
    $sinfo=$db->dblink->serverInfo();
    if(isset($sinfo['version']) && version_compare($sinfo['version'], '5.5.3')>=0 ){
        $db->query('ALTER TABLE {prefs} CHANGE `pref_value` `pref_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
    }else{
        $db->query('ALTER TABLE {prefs} CHANGE `pref_value` `pref_value` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');
    }
}
else{
    $db->query('ALTER TABLE {prefs} CHANGE `pref_value` `pref_value` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');   
}
?>
