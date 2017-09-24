<?php

require_once 'vendor/dapphp/securimage/securimage.php';

$img = new Securimage();
$img->show();  // outputs the image and http headers
