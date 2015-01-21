<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2006  by Cristian Rodriguez R <judas.iscariote@flyspray.org>
// | Copyright (C) 2007  by Florian  Florian Schmitz <floele@flyspray.org>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//

@set_time_limit(0);
//do it fast damn it
ini_set('memory_limit', '64M');

// define basic stuff first.
define('IN_FS', 1);
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
require_once OBJECTS_PATH . '/class.flyspray.php';
define('CONFIG_PATH', Flyspray::get_config_path(APPLICATION_PATH));

define('TEMPLATE_FOLDER', BASEDIR . '/templates/');
$conf  = @parse_ini_file(CONFIG_PATH, true) or die('Cannot open config file at ' . CONFIG_PATH);

$borked = str_replace( 'a', 'b', array( -1 => -1 ) );

if(!isset($borked[-1])) {
    die("Flyspray cannot run here, sorry :-( \n PHP 4.4.x/5.0.x is buggy on your 64-bit system; you must upgrade to PHP 5.1.x\n" .
        "or higher. ABORTING. (http://bugs.php.net/bug.php?id=34879 for details)\n");
}

require_once OBJECTS_PATH . '/fix.inc.php';
require_once OBJECTS_PATH . '/class.gpc.php';

// Use composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Initialise DB
require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb.inc.php';
require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb-xmlschema03.inc.php';

$db = new Database;
$db->dbOpenFast($conf['database']);

// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
$fs = new Flyspray();

define('APPLICATION_SETUP_INDEX', Flyspray::absoluteURI());
define('UPGRADE_VERSION', Flyspray::base_version($fs->version));

define('DOMAIN_HASH', md5($_SERVER['SERVER_NAME'] . (int) $_SERVER['SERVER_PORT']));
define('CACHE_DIR', Flyspray::get_tmp_dir() . DIRECTORY_SEPARATOR . DOMAIN_HASH);

// Get installed version
$sql = $db->Query('SELECT pref_value FROM {prefs} WHERE pref_name = ?', array('fs_ver'));
$installed_version = $db->FetchOne($sql);

$page = new Tpl;
$page->assign('title', 'Upgrade ');
$page->assign('short_version', UPGRADE_VERSION);

//cleanup
//the cache dir
@rmdirr(sprintf('%s/cache/dokuwiki', APPLICATION_PATH));

// ---------------------------------------------------------------------
// Now the hard work
// ---------------------------------------------------------------------

// Find out which upgrades need to be run
$folders = glob_compat(BASEDIR . '/upgrade/[0-9]*');
usort($folders, 'version_compare'); // start with lowest version

if (Post::val('upgrade')) {
    $db->dblink->StartTrans();
    foreach ($folders as $folder) {
        if (version_compare($installed_version, $folder, '<=')) {
            execute_upgrade_file($folder, $installed_version);
            $installed_version = $folder;
        }
    }
    // Update existing projects to default field visibility.
    $db->Query('UPDATE {projects} SET visible_fields = \'tasktype category severity priority status private assignedto reportedin dueversion duedate progress os votes\' WHERE visible_fields = \'\'');
    
    // we should be done at this point
    $db->Query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array($fs->version, 'fs_ver'));
    $db->dblink->CompleteTrans();
    $installed_version = $fs->version;
}

function execute_upgrade_file($folder, $installed_version)
{
    global $db, $page, $conf;
    // At first the config file
    $upgrade_path = BASEDIR . '/upgrade/' . $folder;
    new ConfUpdater(CONFIG_PATH, $upgrade_path);

    $upgrade_info = parse_ini_file($upgrade_path . '/upgrade.info', true);
    // dev version upgrade?
    if ($folder == Flyspray::base_version($installed_version)) {
        $type = 'develupgrade';
    } else {
        $type = 'defaultupgrade';
    }

    // Next a mix of XML schema files and PHP upgrade scripts
    if (!isset($upgrade_info[$type])) {
        die('#1 Bad upgrade.info file.');
    }

    ksort($upgrade_info[$type]);
    foreach ($upgrade_info[$type] as $file) {
        if (substr($file, -4) == '.php') {
            require_once $upgrade_path . '/' . $file;
        }

        if (substr($file, -4) == '.xml') {
            $schema = new adoSchema($db->dblink);
            $xml = file_get_contents($upgrade_path . '/' . $file);
            $xml = str_replace('<table name="', '<table name="' . $conf['database']['dbprefix'], $xml);
            $schema->ParseSchemaString($xml);
            $schema->ExecuteSchema(null, true);
        }
    }

    // Last but not least global prefs update
    if (isset($upgrade_info['fsprefs'])) {
        $sql = $db->Query('SELECT pref_name FROM {prefs}');
        $existing = $db->FetchCol($sql);
        // Add what is missing
        foreach ($upgrade_info['fsprefs'] as $name => $value) {
            if (!in_array($name, $existing)) {
                $db->Query('INSERT INTO {prefs} (pref_name, pref_value) VALUES (?, ?)', array($name, $value));
            }
        }
        // Delete what is too much
        foreach ($existing as $name) {
            if (!isset($upgrade_info['fsprefs'][$name])) {
                $db->Query('DELETE FROM {prefs} WHERE pref_name = ?', array($name));
            }
        }
    }

    $db->Query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array(basename($upgrade_path), 'fs_ver'));
    $page->assign('done', true);
}

 /**
  * Delete a file, or a folder and its contents
  *
  * @author      Aidan Lister <aidan@php.net>
  * @version     1.0.3
  * @link        http://aidanlister.com/repos/v/function.rmdirr.php
  * @param       string   $dirname    Directory to delete
  * @return      bool     Returns TRUE on success, FALSE on failure
  * @license     Public Domain.
  */

 function rmdirr($dirname)
 {
     // Sanity check
     if (!file_exists($dirname)) {
         return false;
     }

     // Simple delete for a file
     if (is_file($dirname) || is_link($dirname)) {
         return unlink($dirname);
     }

     // Loop through the folder
     $dir = dir($dirname);
     while (false !== $entry = $dir->read()) {
         // Skip pointers
         if ($entry == '.' || $entry == '..') {
             continue;
         }
           // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
     }
     // Clean up
     $dir->close();
     return rmdir($dirname);
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
    function ConfUpdater($location, $upgrade_path)
    {
        if (!is_writable($location)) {
            return false;
        }

        $this->old_config = parse_ini_file($location, true) or die('Aborting: Could not open config file at ' . $location);
        $this->new_config = parse_ini_file($upgrade_path . '/flyspray.conf.php', true);
        // Now we overwrite all values of the *default* file if there is one in the existing config
        array_walk($this->new_config, array($this, '_merge_configs'));
        // save custom attachment definitions
        $this->new_config['attachments'] = $this->old_config['attachments'];

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
            // Upgrade to MySQLi if possible
            if ($key == 'dbtype' && strtolower($settings[$key]) == 'mysql' && function_exists('mysqli_connect')) {

                //mysqli is broken on 64bit systems in versions < 5.1 do not use it, tested, does not work.
                if (php_uname('m') == 'x86_64' && version_compare(phpversion(), '5.1.0', '<')) {
                    continue;
                }

                $settings[$key] = 'mysqli';
            }
            //no matter what, change the randomization key on each upgrade as an extra security improvement.
            if($key === 'cookiesalt') {
               $settings[$key] = md5(uniqid(mt_rand(), true));
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
                if (is_array($value)) {
                    foreach ($value as $_key => $_value) {
                        $new_config .= sprintf('%s="%s"', "{$key}[{$_key}]", addslashes($_value)). "\n";
                    }
                } else {
                    $new_config .= sprintf('%s="%s"', $key, addslashes($value)). "\n";
                }
            }
            $new_config .= "\n";
        }

        $fp = fopen($location, 'wb');
        fwrite($fp, $new_config);
        fclose($fp);
    }
}

function postgresql_adodb() {
    if (class_exists('ReflectionClass')) {
        require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb-datadict.inc.php';
        require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/datadict/datadict-postgres.inc.php';
	$refclass = new ReflectionClass('ADODB2_postgres');
	$refmethod = $refclass->getMethod('ChangeTableSQL');
	$implclass = $refmethod->getDeclaringClass();
	if ($implclass->name === 'ADODB2_postgres') {
	    return true;
	}
	return false;
    } else {
	// Can't even do the test, hope the user is able to handle the situation him/serself.
	return true;
    }
}

$checks = $todo = array();
$checks['version_compare'] = version_compare($installed_version, UPGRADE_VERSION) === -1;
$checks['config_writable'] = is_writable(CONFIG_PATH);
$checks['db_connect'] = (bool) $db->dblink;
$checks['installed_version'] = version_compare($installed_version, '0.9.6') === 1;
$todo['config_writable'] = 'Please make sure that the file at ' . CONFIG_PATH . ' is writable.';
$todo['db_connect'] = 'Connection to the database could not be established. Check your config.';
$todo['version_compare'] = 'No newer version than yours can be installed with this upgrader.';
$todo['installed_version'] = 'An upgrade from Flyspray versions lower than 0.9.6 is not possible.
                              You will have to upgrade manually to at least 0.9.6, the scripts which do that are included in all Flyspray releases <= 0.9.8.';

if ($conf['database']['dbtype'] == 'pgsql') {
    $checks['postgresql_adodb'] = (bool) postgresql_adodb();
    $todo['postgresql_adodb'] = 'You have a version of ADOdb that does not contain overridden version of method ChangeTableSQL for PostgreSQL. '
	    . 'Please copy setup/upgrade/1.0/datadict-postgres.inc.php to '
	    . 'vendor/adodb/adodb-php/datadict/ before proceeding with the upgrade process.';
}

$upgrade_possible = true;
foreach ($checks as $check => $result) {
    if ($result !== true) {
        $upgrade_possible = false;
        $page->assign('todo', $todo[$check]);
        break;
    }
}

if (isset($upgrade_info['options'])) {
    // piece of HTML which adds user input, quick and dirty*/
    $page->assign('upgrade_options', implode('', $upgrade_info['options']));
}

$page->assign('index', APPLICATION_SETUP_INDEX);
$page->uses('checks', 'fs', 'upgrade_possible');
$page->assign('installed_version', $installed_version);

$page->display('upgrade.tpl');

