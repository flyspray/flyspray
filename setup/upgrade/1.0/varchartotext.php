<?php

$db->Query('ALTER TABLE {prefs} CHANGE `pref_value` `pref_value` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');

?>