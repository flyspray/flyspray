<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2006  by Cristian Rodriguez R <soporte@onfocus.cl>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//

session_start();

set_time_limit(0);
ini_set('memory_limit', '32M');

// define basic stuff first.
define('IN_FS', 1 );
define('APPLICATION_NAME', 'Flyspray');
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');
$conf['general']['syntax_plugin'] = '';


require_once OBJECTS_PATH . '/fix.inc.php';
require_once OBJECTS_PATH . '/class.gpc.php';
require_once OBJECTS_PATH . '/class.flyspray.php';
require_once OBJECTS_PATH . '/class.tpl.php';
require_once OBJECTS_PATH . '/version.php';

// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
define('APPLICATION_SETUP_INDEX', Flyspray::absoluteURI());
define('XMLS_DEBUG', true);
define('XMLS_PREFIX_MAXLEN', 15);

$version = new Version();
$page = new Tpl;
$page->assign('title', 'Upgrade ');
$page->assign('product_name', $version->mProductName);
$page->assign('version', $version->mVersion);
$page->assign('installed_version', 'X'); // get version info from database
$page->assign('short_version', $version->mRelease . '.' . $version->mDevLevel);
$page->assign('copyright', $version->mCopyright);

// ---------------------------------------------------------------------
// Now the hard work
// ---------------------------------------------------------------------

if (Post::val('upgrade')) {
    $conf = new ConfUpdater(APPLICATION_PATH . '/flyspray.conf.php', $version);
}
    
function is_true($var)
{
    return $var === true;
}

class ConfUpdater
{
    var $old_config = array();
    var $new_config = array();
    
    /**
     * Reads the existing config file and updates it
     * @param string $location
     * @access public
     * @return bool
     */
    function ConfUpdater($location, $version)
    {
        if (!is_writable($location)) {
            return false;
        }
        
        $this->old_config = parse_ini_file($location, true);
        $this->new_config = parse_ini_file(BASEDIR . '/upgrade/' . $version->mRelease . '.' . $version->mDevLevel . '/flyspray.conf.php', true);
        // Now we overwrite all values of the *default* file if there is one in the existing config
        array_walk($this->new_config, array($this, '_merge_configs'));
        
        $this->_write_config($location);
        
    }

    /**
     * Callback function, merges config values
     * @param array $settings
     * @access private
     * @return array
     */
    function _merge_configs(&$settings, $group)
    {
        foreach ($settings as $key => $value) {
            if (isset($this->old_config[$group][$key])) {
                $settings[$key] = $this->old_config[$group][$key];
            }                
        }
    }

    /**
     * Writes the new config file to a given $location
     * @param string $location
     * @access private
     */
    function _write_config($location)
    {
        $new_config = "; <?php die( 'Do not access this page directly.' ); ?>\n\n";
        foreach ($this->new_config as $group => $settings) {
            $new_config .= "[{$group}]\n";
            foreach ($settings as $key => $value) {
                $new_config .= $key . '="' . str_replace('"', '\"', $value) . '"' . "\n";
            }
            $new_config .= "\n";
        }

        $fp = fopen($location, 'w');
        fwrite($fp, $new_config);
        fclose($fp);
    }
}

$checks = array();
$checks['config_writable'] = is_writable(APPLICATION_PATH . '/flyspray.conf.php');

$page->assign('upgrade_options', '<div><label><input type="checkbox" />
                                  Replace resolution list (strongly recommended)
                                  </label></div>'); // piece of HTML which adds user input, quick and dirty
$page->assign('todo', 'Says what needs to be done to make the upgrader work.'); // check if file is writable
$page->assign('upgrade_possible', count(array_filter($checks, 'is_true'))); // version compare (consider development versions!), flyspray.conf check

$page->assign('index', APPLICATION_SETUP_INDEX);
$page->uses('checks');

$page->display('upgrade.tpl');

?>
