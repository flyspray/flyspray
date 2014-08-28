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

require_once("includes.php");

//use the restler autoloader to load all api classes.
spl_autoload_register(AutoLoader::instance());

//allow the API Explorer to display API calls that are protected by user / group authentication.
Resources::$hideProtected = false;

//check to determine if configuration settings prevent the use of web services.
if($config['allow_web_services']==1)
{
    $r= new Restler();
    $r->setSupportedFormats('JsonFormat', 'XmlFormat');
    $r->addAPIClass('api_Effort');
    $r->addAPIClass('api_Groups');
    $r->addAPIClass('api_Projects');
    $r->addAPIClass('api_Tasks');
    $r->addAPIClass('api_Users');
    $r->addAPIClass('Luracast\\Restler\\Resources');
    $r->handle();
}
else
{
    //Return a 403 forbidden message for all routes within the system.
    header('HTTP/1.0 403 Forbidden');
    echo "Your flyspray administrator has disabled web services for this instance of flyspray.";
}

