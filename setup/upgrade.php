<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2006  by Cristian Rodriguez R
// | Copyright (C) 2007  by Florian Schmitz
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

#define('DEBUG_SQL', 1);

define('TEMPLATE_FOLDER', BASEDIR . '/templates/');
$conf  = @parse_ini_file(CONFIG_PATH, true) or die('Cannot open config file at ' . CONFIG_PATH);

$borked = str_replace( 'a', 'b', array( -1 => -1 ) );

if(!isset($borked[-1])) {
    die("Flyspray cannot run here, sorry :-( \n PHP 4.4.x/5.0.x is buggy on your 64-bit system; you must upgrade to PHP 5.1.x\n" .
        "or higher. ABORTING. (http://bugs.php.net/bug.php?id=34879 for details)\n");
}

require_once OBJECTS_PATH . '/fix.inc.php';
require_once OBJECTS_PATH . '/class.gpc.php';
require_once OBJECTS_PATH . '/i18n.inc.php';

# fake objects for load_translation()
class user{var $infos=array();}; class project{var $id=0;};
$user=new user; $proj=new project;
load_translations();

// Use composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Initialise DB
require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb.inc.php';
require_once dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb-xmlschema03.inc.php';

$db = new Database;
$db->dbOpenFast($conf['database']);

$webdir = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'utf-8'));
$baseurl = rtrim(Flyspray::absoluteURI($webdir),'/\\') . '/' ;

// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
$fs = new Flyspray();

define('APPLICATION_SETUP_INDEX', Flyspray::absoluteURI());
define('UPGRADE_VERSION', Flyspray::base_version($fs->version));

define('DOMAIN_HASH', md5($_SERVER['SERVER_NAME'] . (int) $_SERVER['SERVER_PORT']));
define('CACHE_DIR', Flyspray::get_tmp_dir() . DIRECTORY_SEPARATOR . DOMAIN_HASH);

// Get installed version
$sql = $db->query('SELECT pref_value FROM {prefs} WHERE pref_name = ?', array('fs_ver'));
$installed_version = $db->fetchOne($sql);

$page = new Tpl;
$page->assign('title', 'Upgrade ');
$page->assign('short_version', UPGRADE_VERSION);

if (!isset($conf['general']['syntax_plugin']) || !$conf['general']['syntax_plugin'] || $conf['general']['syntax_plugin'] == 'none') {
    $page->assign('ask_for_conversion', true);
} else {
    $page->assign('ask_for_conversion', false);
}

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
	$uplog=array();
	$uplog[]="Start database transaction";
	$db->dblink->startTrans();
	fix_duplicate_list_entries(true);
	foreach ($folders as $folder) {
		if (version_compare($installed_version, $folder, '<=')) {
			$uplog[]="Start <strong>$installed_version</strong> to <strong>$folder</strong>";
			$uplog[]= execute_upgrade_file($folder, $installed_version);
			$uplog[]="End <strong>$installed_version</strong> to <strong>$folder</strong>";
			$installed_version = $folder;
		}
	}

    # maybe as Filter: $out=html2wiki($input, 'wikistyle'); and $out=wiki2html($input, 'wikistyle') ?
    # No need for any filter, because dokuwiki format wouldn't be touched anyway. But maybe ask the user
    # first and explain that html-formatting is now used instead of plain text on installations that didn't
    # use dokuwiki format. Then, adding paragraph tags and line breaks might enhance readability.
    // For testing, do not use yet, have to discuss this one with others.
    if ((!isset($conf['general']['syntax_plugin']) || !$conf['general']['syntax_plugin'] || $conf['general']['syntax_plugin'] == 'none') && Post::val('yes_please_do_convert')) {
        convert_old_entries('tasks', 'detailed_desc', 'task_id');
        convert_old_entries('projects', 'intro_message', 'project_id');
        convert_old_entries('projects', 'default_task', 'project_id');
        convert_old_entries('comments', 'comment_text', 'comment_id');
        $page->assign('conversion', true);
    } else {
        $page->assign('conversion', false);
    }

    // Fix the sequence in tasks table for PostgreSQL.
    if ($db->dblink->dataProvider == 'postgres') {
        $rslt = $db->query('SELECT MAX(task_id) FROM {tasks}');
        $maxid = $db->fetchOne($rslt);
        // The correct sequence should normally have a name containing at least both the table and column name in this format. 
        $rslt = $db->query('SELECT relname FROM pg_class WHERE NOT relname ~ \'pg_.*\' AND relname LIKE \'%' . $conf['database']['dbprefix'] . 'tasks_task_id%\' AND relkind = \'S\'');
        if ($db->countRows($rslt) == 1) {
            $seqname = $db->fetchOne($rslt);
            $db->query('SELECT setval(?, ?)', array($seqname, $maxid));
        }
    }

	// we should be done at this point
	$db->query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array($fs->version, 'fs_ver'));
	$uplog[]="Final Step: Set version in {prefs} to the version in class.flyspray.php: End <strong>$installed_version</strong> to <strong>$fs->version</strong>";

	$result=$db->dblink->completeTrans();
        $uplog[]= 'Transaction completed: '.$result;
	
	$installed_version = $fs->version;
	$page->assign('done', true);
	$page->assign('upgradelog', $uplog);
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
            // $xml = str_replace('<table name="', '<table name="' . $conf['database']['dbprefix'], $xml);
            // Set the prefix for database objects ( before parsing)
            $schema->setPrefix($conf['database']['dbprefix'], false);
            $schema->parseSchemaString($xml);
            $schema->executeSchema(null, true);
        }
    }

    // Last but not least global prefs update
    if (isset($upgrade_info['fsprefs'])) {
        $sql = $db->query('SELECT pref_name FROM {prefs}');
        $existing = $db->fetchCol($sql);
        // Add what is missing
        foreach ($upgrade_info['fsprefs'] as $name => $value) {
            if (!in_array($name, $existing)) {
                $db->query('INSERT INTO {prefs} (pref_name, pref_value) VALUES (?, ?)', array($name, $value));
            }
        }
        // Delete what is too much
        foreach ($existing as $name) {
            if (!isset($upgrade_info['fsprefs'][$name])) {
                $db->query('DELETE FROM {prefs} WHERE pref_name = ?', array($name));
            }
        }
    }

    $db->query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array(basename($upgrade_path), 'fs_ver'));
    #$page->assign('done', true);
    return "Wrote <strong>".basename($upgrade_path)."</strong> into database table {prefs} field fs_ver.";
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
        # first try to keep an existing oauth config on upgrades
        $this->new_config['oauth'] = $this->old_config['oauth'];

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

// Functions for checking and fixing possible duplicate entries
// in database for those tables that now have a unique index.

function fix_duplicate_list_entries($doit=true) {
    global $db,$uplog;

    // Categories need a bit more thinking. A real life example from
    // my own database: A big project originally written (horrible!)
    // in VB6, that I ported to .NET -environment. Categories:
    // BackOfficer (main category)
    // -> Reports (subcategory - should be allowed)
    // BackOfficer.NET (main category)
    // -> Reports (subcategory - should be allowed)
    // -> Reports (I added a fake duplicate - should not be allowed)

    $sql = $db->query('SELECT MIN(os_id) id, project_id, os_name
                          FROM {list_os}
                      GROUP BY project_id, os_name
                        HAVING COUNT(*) > 1');
    $dups = $db->fetchAllArray($sql);
    if (count($dups) > 0) {
        if($doit){
            fix_os_table($dups);
        } else{
            $uplog[]='<span class="warning">'.count($dups).' duplicate entries in {list_os}</span>';
        }
    }

    $sql = $db->query('SELECT MIN(resolution_id) id, project_id, resolution_name
                          FROM {list_resolution}
                      GROUP BY project_id, resolution_name
                        HAVING COUNT(*) > 1');
    $dups = $db->fetchAllArray($sql);
    if (count($dups) > 0) {
        if($doit){
            fix_resolution_table($dups);
        }else{
            $uplog[]='<span class="warning">'.count($dups).' duplicate entries in {list_resolution}</span>';
        }
    }

    $sql = $db->query('SELECT MIN(status_id) id, project_id, status_name
                          FROM {list_status}
                      GROUP BY project_id, status_name
                        HAVING COUNT(*) > 1');
    $dups = $db->fetchAllArray($sql);
    if (count($dups) > 0) {
        if($doit){
            fix_status_table($dups);
        }else{
            $uplog[]='<span class="warning">'.count($dups).' duplicate entries in {list_status}</span>';
        }
    }
    $sql = $db->query('SELECT MIN(tasktype_id) id, project_id, tasktype_name
                          FROM {list_tasktype}
                      GROUP BY project_id, tasktype_name
                        HAVING COUNT(*) > 1');
    $dups = $db->fetchAllArray($sql);
    if (count($dups) > 0) {
        if($doit){
            fix_tasktype_table($dups);
        }else{
            $uplog[]='<span class="warning">'.count($dups).' duplicate entries in {list_tasktype}</span>';
        }
    }

    $sql = $db->query('SELECT MIN(version_id) id, project_id, version_name
                          FROM {list_version}
                      GROUP BY project_id, version_name
                        HAVING COUNT(*) > 1');
    $dups = $db->fetchAllArray($sql);
    if (count($dups) > 0) {
        if($doit){
            fix_version_table($dups);
        }else{
            $uplog[]='<span class="warning">'.count($dups).' duplicate entries in {list_version}</span>';
        }
    }
}

function fix_os_table($dups) {
    global $db;

    foreach ($dups as $dup) {
        $update_id = $dup['id'];

        $sql = $db->query('SELECT os_id id
                             FROM {list_os}
                            WHERE project_id = ? AND os_name = ?',
                          array($dup['project_id'], $dup['os_name']));
        $entries = $db->fetchAllArray($sql);
        foreach ($entries as $entry) {
            if ($entry['id'] == $update_id) {
                continue;
            }

            $db->query('UPDATE {tasks}
                           SET operating_system = ?
                         WHERE operating_system = ?',
                       array($update_id, $entry['id']));
            $db->query('DELETE FROM {list_os} WHERE os_id = ?', array($entry['id']));
        }
    }
}

function fix_resolution_table($dups) {
    global $db;

    foreach ($dups as $dup) {
        $update_id = $dup['id'];

        $sql = $db->query('SELECT resolution_id id
                             FROM {list_resolution}
                            WHERE project_id = ? AND resolution_name = ?',
                          array($dup['project_id'], $dup['resolution_name']));
        $entries = $db->fetchAllArray($sql);
        foreach ($entries as $entry) {
            if ($entry['id'] == $update_id) {
                continue;
            }

            $db->query('UPDATE {tasks}
                           SET resolution_reason = ?
                         WHERE resolution_reason = ?',
                       array($update_id, $entry['id']));
            $db->query('DELETE FROM {list_resolution} WHERE resolution_id = ?', array($entry['id']));
        }
    }
}

function fix_status_table($dups) {
    global $db;

    foreach ($dups as $dup) {
        $update_id = $dup['id'];

        $sql = $db->query('SELECT status_id id
                             FROM {list_status}
                            WHERE project_id = ? AND status_name = ?',
                          array($dup['project_id'], $dup['status_name']));
        $entries = $db->fetchAllArray($sql);
        foreach ($entries as $entry) {
            if ($entry['id'] == $update_id) {
                continue;
            }

            $db->query('UPDATE {tasks}
                           SET item_status = ?
                         WHERE item_status = ?',
                       array($update_id, $entry['id']));
            $db->query('DELETE FROM {list_status} WHERE status_id = ?', array($entry['id']));
        }
    }
}

function fix_tasktype_table($dups) {
    global $db;

    foreach ($dups as $dup) {
        $update_id = $dup['id'];

        $sql = $db->query('SELECT tasktype_id id
                             FROM {list_tasktype}
                            WHERE project_id = ? AND tasktype_name = ?',
                          array($dup['project_id'], $dup['tasktype_name']));
        $entries = $db->fetchAllArray($sql);
        foreach ($entries as $entry) {
            if ($entry['id'] == $update_id) {
                continue;
            }

            $db->query('UPDATE {tasks}
                           SET task_type = ?
                         WHERE task_type = ?',
                       array($update_id, $entry['id']));
            $db->query('DELETE FROM {list_tasktype} WHERE tasktype_id = ?', array($entry['id']));
        }
    }
}

function fix_version_table($dups) {
    global $db;

    foreach ($dups as $dup) {
        $update_id = $dup['id'];

        $sql = $db->query('SELECT version_id id
                             FROM {list_version}
                            WHERE project_id = ? AND version_name = ?',
                          array($dup['project_id'], $dup['version_name']));
        $entries = $db->fetchAllArray($sql);
        foreach ($entries as $entry) {
            if ($entry['id'] == $update_id) {
                continue;
            }

            $db->query('UPDATE {tasks}
                           SET product_version = ?
                         WHERE product_version = ?',
                       array($update_id, $entry['id']));
            $db->query('DELETE FROM {list_version} WHERE version_id = ?', array($entry['id']));
        }
    }
}

// Just a sketch on how database columns could be updated to the new format.
// Not tested for errors or used anywhere yet.

function convert_old_entries($table, $column, $key) {
    global $db;

    // Assuming that anything not beginning with < was made with older
    // versions of flyspray. This will not catch neither those old entries
    // where the user for some reason really added paragraph tags nor those
    // made with development version before fixing ckeditors configuration
    // settings. You can't have everything in a limited time frame, this
    // should be just good enough.
    $sql = $db->query("SELECT $key, $column "
            . "FROM {". $table . "} "
            . "WHERE $column NOT LIKE '<%'");
    $entries = $db->fetchAllArray($sql);

    # We should probably better use existing and proven filters for the conversions
    # maybe this or existing dokuwiki functionality?
    # $out=html2wiki($input, 'wikistyle'); and $out=wiki2html($input, 'wikistyle')

    foreach ($entries as $entry) {
        $id = $entry[$key];
        $data = $entry[$column];

	if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else{
                $data = htmlspecialchars($data, ENT_QUOTES , 'UTF-8');
        }
        // Convert two or more line breaks to paragrahs, Windows/Unix/Linux formats
        $data = preg_replace('/(\h*\r?\n)+\h*\r?\n/', "</p><p>", $data);
        // Data coming from Macs has only carriage returns, and couldn't say
        // \r?\n? in the previous regex, it would also have matched nothing.
        // Even a short word like "it" has three nothings in it, one before
        // i, one between i and t and one after t...
        $data = preg_replace('/(\h*\r)+\h*\r/', "</p><p>", $data);
        // Remaining single line breaks
        $data = preg_replace('/\h*\r?\n/', "<br/>", $data);
        $data = preg_replace('/\h*\r/', "<br/>", $data);
        // Remove final extra break, if the data to converted ended with a line break
        $data = preg_replace('#<br/>$#', '', $data);
        // Remove final extra paragraph tags, if the data to converted ended with
        // more than one line breaks
        $data = preg_replace('#</p><p>$#', '', $data);
        // Enclose the whole in paragraph tags, so it looks
        // the same as what ckeditor produces.
        $data = '<p>' . $data . '</p>';

        $db->query("UPDATE {". $table . "} "
        . "SET $column = ?"
        . "WHERE $key = ?",
        array($data, $id));
    }
}
