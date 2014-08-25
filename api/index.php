<?php
/**
 * Flyspray REST Services
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray/api
 * @author Steven Tredinnick
 */
use \Luracast\Restler\Restler;
use \Luracast\Restler\AutoLoader;
use \Luracast\Restler\Resources;

require_once("../vendor/restler/framework/Luracast/Restler/AutoLoader.php");
require_once("includes.php");

spl_autoload_register(AutoLoader::instance());


Resources::$hideProtected = false;

$r= new Restler();
$r->setSupportedFormats('JsonFormat','XmlFormat');
$r->addAPIClass('api_Effort');
$r->addAPIClass('api_Groups');
$r->addAPIClass('api_Projects');
$r->addAPIClass('api_Tasks');
$r->addAPIClass('api_Users');
$r->addAPIClass('Luracast\\Restler\\Resources');
$r->addAuthenticationClass('AccessControl');
$r->handle();


