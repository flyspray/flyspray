<?php

  /*********************************************************\
  | flyspray daemon                                         |
  | ~~~~~~~~~~~~~~~                                         |
  \*********************************************************/

$include = $_SERVER['argv'][1];
$sleep   = $_SERVER['argv'][2];
$runfile = $_SERVER['argv'][3];

chdir(dirname(__FILE__));

while (touch($runfile)) {
    run($include);
    sleep($sleep);
}

function run($include)
{
    global $db, $fs;
    include $include;
}

?>
