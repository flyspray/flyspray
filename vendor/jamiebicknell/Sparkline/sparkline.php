<?php

/*
Title:      Sparkline
URL:        http://github.com/jamiebicknell/Sparkline
Author:     Jamie Bicknell
Twitter:    @jamiebicknell
*/

function isHex($string)
{
    return preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $string);
}

function hexToRgb($hex)
{
    $hex = ltrim(strtolower($hex), '#');
    $hex = isset($hex[3]) ? $hex : $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    $dec = hexdec($hex);
    return array(0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec);
}

$size = isset($_GET['size']) ? str_replace('x', '', $_GET['size']) != '' ? $_GET['size'] : '80x20' : '80x20';
$back = isset($_GET['back']) ? isHex($_GET['back']) ? $_GET['back'] : 'ffffff' : 'ffffff';
$line = isset($_GET['line']) ? isHex($_GET['line']) ? $_GET['line'] : '1388db' : '1388db';
$fill = isset($_GET['fill']) ? isHex($_GET['fill']) ? $_GET['fill'] : 'e6f2fa' : 'e6f2fa';
$data = isset($_GET['data']) ? explode(',', $_GET['data']) : array();

list($w, $h) = explode('x', $size);
$w = floor(max(50, min(800, $w)));
$h = !strstr($size, 'x') ? $w : floor(max(20, min(800, $h)));
$t = 1.75;
$s = 4;

$w *= $s;
$h *= $s;
$t *= $s;

$salt = 'v1.0.0';
$hash = md5($salt . $_SERVER['QUERY_STRING']);

$data = (count($data) < 2) ? array_fill(0, 2, $data[0]) : $data;
$count = count($data);
$step = $w / ($count - 1);
$max = max($data);

if (!extension_loaded('gd')) {
    die('GD extension is not installed');
}

if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    if ($_SERVER['HTTP_IF_NONE_MATCH'] == $hash) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
        die();
    }
}

$im = imagecreatetruecolor($w, $h);
list($r, $g, $b) = hexToRgb($back);
$bg = imagecolorallocate($im, $r, $g, $b);
list($r, $g, $b) = hexToRgb($line);
$fg = imagecolorallocate($im, $r, $g, $b);
list($r, $g, $b) = hexToRgb($fill);
$lg = imagecolorallocate($im, $r, $g, $b);
imagefill($im, 0, 0, $bg);

imagesetthickness($im, $t);

foreach ($data as $k => $v) {
    $v = $v > 0 ? round($v / $max * $h) : 0;
    $data[$k] = max($s, min($v, $h - $s));
}

$x1 = 0;
$y1 = $h - $data[0];
$line = array();
$poly = array(0, $h + 50, $x1, $y1);
for ($i = 1; $i < $count; $i++) {
    $x2 = $x1 + $step;
    $y2 = $h - $data[$i];
    array_push($line, array($x1, $y1, $x2, $y2));
    array_push($poly, $x2, $y2);
    $x1 = $x2;
    $y1 = $y2;
}
array_push($poly, $x2, $h + 50);

imagefilledpolygon($im, $poly, $count + 2, $lg);

foreach ($line as $k => $v) {
    list($x1, $y1, $x2, $y2) = $v;
    imageline($im, $x1, $y1, $x2, $y2, $fg);
}

$om = imagecreatetruecolor($w / $s, $h / $s);
imagecopyresampled($om, $im, 0, 0, 0, 0, $w / $s, $h / $s, $w, $h);
imagedestroy($im);

header('Content-Type: image/png');
header('Content-Disposition: inline; filename="sparkline_' . time() . substr(microtime(), 2, 3) . '.png"');
header('ETag: ' . $hash);
header('Accept-Ranges: none');
header('Cache-Control: max-age=604800, must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s T', strtotime('+7 days')));
imagepng($om);
imagedestroy($om);
