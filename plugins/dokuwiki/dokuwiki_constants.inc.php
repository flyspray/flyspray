<?php
define('FLYSPRAY_USE_CACHE', true);
define('FLYSPRAY_HAS_PREVIEW', true);
define('DOKU_PLUGIN',        BASEDIR . '/plugins/dokuwiki/lib/plugins/');
define('DOKU_CONF',          BASEDIR . '/plugins/dokuwiki/conf/');
define('DOKU_INTERNAL_LINK', $conf['general']['doku_url']);
define('DOKU_BASE',          $baseurl .'plugins/dokuwiki/');
define('DOKU_URL',           BASEDIR .'/plugins/dokuwiki/');
?>