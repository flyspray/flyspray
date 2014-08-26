<?php
/**
 * Flyspray REST Services
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray/api
 * @author Steven Tredinnick
 */


require_once("../vendor/restler/framework/Luracast/Restler/AutoLoader.php");
require_once('database.php');

$config = parse_ini_file('../flyspray.conf.php');

//include all of the files in the apis folder

foreach (glob("apis/*.php") as $filename)
{
    include $filename;
}