<?php

$db->query('UPDATE {users} SET email_address = LOWER(email_address), jabber_id = LOWER(jabber_id)');

?>

