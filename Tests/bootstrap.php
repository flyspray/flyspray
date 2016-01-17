<?php
/*
function loader($class) {
  $file = $class . '.php';
  if (file_exists($file)) {
    require $file;
  }
}

spl_autoload_register('loader');
*/

define('IN_FS', true); # for passing some checks in Flyspray files

if(is_readable('vendor/autoload.php')){
  // Use composer autoloader
  require 'vendor/autoload.php';
}
?>
