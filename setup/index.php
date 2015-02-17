<?php
// +----------------------------------------------------------------------
// | Installer - there is still a lot to clean up, but it works
// +----------------------------------------------------------------------
// | Copyright (C) 2005 by Jeffery Fernandez <developer@jefferyfernandez.id.au>
// | Copyright (C) 2006-2007  by Cristian Rodriguez <judas.iscariote@flyspray.org> and Florian Schmitz <floele@gmail.com>
// +----------------------------------------------------------------------

@set_time_limit(0);

// no transparent session id improperly configured servers
ini_set('session.use_trans_sid', 0);

session_start();
//do it fastest as possible.
ini_set('memory_limit', '64M');


if (is_readable ('../flyspray.conf.php') && count(parse_ini_file('../flyspray.conf.php')) > 0)
{
   die('Flyspray already installed. Use the <a href="upgrade.php">upgrader</a> to upgrade your Flyspray, or delete flyspray.conf.php to run setup.
        You can *not* use the setup on an existing database.');
}

$borked = str_replace('a', 'b', array( -1 => -1 ) );

if(!isset($borked[-1])) {
    die("Flyspray cannot run here, sorry :-( PHP 4.4.x/5.0.x is buggy on your 64-bit system; you must upgrade to PHP 5.1.x\n" .
        "or higher. ABORTING. (http://bugs.php.net/bug.php?id=34879 for details)\n");
}

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

// Use composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';
// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
define('APPLICATION_SETUP_INDEX', Flyspray::absoluteURI());

class Setup extends Flyspray
{
   public $mPhpRequired;
   public $mSupportedDatabases;
   public $mAvailableDatabases;

   public $mProceed;
   public $mPhpVersionStatus;
   public $mDatabaseStatus;
   public $xmlStatus;
   public $mConfigText;
   public $mHtaccessText;
   public $mWriteStatus;

   public $mDbConnection;
   public $mProductName;

   /**
    * @var string To store the data filter type
    */
   public $mDataFilter;

   /**
    * @var array To store the type of database setup (install or Upgrade).
    */

   public $mAttachmentsTable;
   public $mCommentsTable;

   public $mServerSoftware;
   public $mMinPasswordLength;
   public $mAdminUsername;
   public $mAdminPassword;
   /**
    * @var object to store the adodb datadict object.
    */
   public $mDataDict;

   public $mXmlSchema;

   public function __construct()
   {
      // Look for ADOdb
      $this->mAdodbPath         = dirname(__DIR__) . '/vendor/adodb/adodb-php/adodb.inc.php';
      $this->mProductName       = 'Flyspray';
      $this->mMinPasswordLength	= 8;

      // Initialise flag for proceeding to next step.
      $this->mProceed				= false;
      $this->mPhpRequired			= '5.2.0';
      $this->xmlStatus = function_exists('xml_parser_create');
      $this->sapiStatus = (php_sapi_name() != 'cgi');

      // If the database is supported in Flyspray, the function to check in PHP.
      $this->mSupportedDatabases	=
                           array(
                                 'MySQLi' => array(true,'mysqli_connect','mysqli'),
                                 'MySQL' => array(true, 'mysql_connect', 'mysql'),
                                 'Postgres' => array(true, 'pg_connect', 'pgsql'),
                              );
      $this->mAvailableDatabases	= array();

      // Process the page actions
      $this->ProcessActions();
   }

   /**
   * Function to check the permission of the config file
   * @param void
   * @return string An html formatted boolean answer
   */
   public function CheckWriteability($path)
   {
      // Get the full path to the file
      $file = APPLICATION_PATH .'/' . $path;

      // In case it is flyspray.conf.php, the file does not exist
      // so we can't tell that it is writeable. So we attempt to create an empty one
      if ($path == 'flyspray.conf.php') {
        $fp = @fopen($file, 'wb');
        @fclose($fp);
      }
      // Let's try at least...
      @chmod($file, 0666);
      $this->mWriteStatus[$path] = $this->IsWriteable($file);

      // Return an html formated writeable/un-writeable string
      return $this->ReturnStatus($this->mWriteStatus[$path], $type = 'writeable');
   }

   /**
   * Function to check the availability of the Database support
   * @param void
   * @return void
   */
   public function CheckDatabaseSupport()
   {
      $status = array();

      foreach ($this->mSupportedDatabases as $which => $database)
      {
      // Checking if the database has libraries built into PHP. Returns true/false
      $this->mAvailableDatabases[$which]['status'] = function_exists($database[1]);

      // If the Application(Flyspray) supports the available database supported in PHP
      $this->mAvailableDatabases[$which]['supported'] = ($database[0] === $this->mAvailableDatabases[$which]['status'])
         ?  $this->mAvailableDatabases[$which]['status']
         :  false;

      // Just transferring the value for ease of veryfying Database support.
      $status[] = $this->mAvailableDatabases[$which]['supported'];

      // Generating the output to be displayed
      $this->mAvailableDatabases[$which]['status_output'] =
         $this->ReturnStatus($this->mAvailableDatabases[$which]['status'], $type = 'available');
      }

      // Check if any one database support exists.
      // Update the status of database availability
      $this->mDatabaseStatus = in_array('1', $status);
   }


   /**
    * CheckPreStatus
    * we proceed or not ?
    * @access public
    * @return bool
    */
   public function CheckPreStatus()
   {
      $this->mProceed = ($this->mDatabaseStatus && $this->mPhpVersionStatus && $this->xmlStatus);

      return $this->mProceed;
   }


   /**
   * Function to check the version of PHP available compared to the
   * Applications requirements
   * @param void
   * @return string An html formatted boolean answer
   */
   public function CheckPhpCompatibility()
   {
      // Check the PHP version. Recommended version is 4.3.9 and above
      $this->mPhpVersionStatus = version_compare(PHP_VERSION, $this->mPhpRequired, '>=');

      // Return an html formated Yes/No string
      return $this->ReturnStatus($this->mPhpVersionStatus, $type = 'yes');
   }

   /**
   * Function to check the posted data from forms.
   * @param array $expectedFields Array of field names which needs processing
   * If the array of filed names don't exist in the Posted data, then this function
   * will accumulate error messages in the $_SESSION[PASS_PHRASE]['page_message'] array.
   * return boolean/array $data will be returned if successful
   */
   public function CheckPostedData($expectedFields, $pageHeading)
   {
       if(!is_array($expectedFields)){
           $expectedFields = array();
       }

      // Grab the posted data and trim it.
      $data = array_filter($_POST, array(&$this, "TrimArgs"));


      // Loop through the required values and check data
      foreach($expectedFields as $key => $value)
      {

         // If the data is Required and is empty or not set
         if (!isset($data[$key]) || empty($data[$key]))
         {
            if ($expectedFields[$key][2] == true)
            {
               // Acumulate error messages
               $_SESSION['page_message'][] = "<strong>{$expectedFields[$key][0]}</strong>  is required";
            }
         }
         // Check for variable types
         elseif (!$this->VerifyVariableTypes($expectedFields[$key][1], $data[$key]))
         {
            $_SESSION['page_message'][] = "<strong>{$expectedFields[$key][0]}</strong> has to be a {$expectedFields[$key][1]}";
         }
      }

      // If there were messages, return false
      if (isset($_SESSION['page_message']))
      {
         $_SESSION['page_heading'] = $pageHeading;
         return false;
      }
      else
      {
         return $data;
      }
   }

   public function DisplayAdministration()
   {
      // Trim the empty values in the $_POST array
      $data = array_filter($_POST, array($this, "TrimArgs"));

      $templates =
      array(
            'admin_body' => array(
                        'path' => TEMPLATE_FOLDER,
                        'template' => 'administration.tpl',
                        'vars' => array(
                                    'product_name' => $this->mProductName,
                                    'message' => $this->GetPageMessage(),
                                    'admin_email' => $this->GetAdminInput('admin_email', $this->GetParamValue($data, 'admin_email', ''), 'Admin Email'),
                                    'pass_phrase' => $this->GetParamValue($data, 'pass_phrase', ''),
                                    'admin_username' => $this->GetAdminInput('admin_username', $this->GetParamValue($data, 'admin_username', ''), 'Admin Username'),
                                    'admin_password' => $this->GetAdminInput('admin_password', $this->GetParamValue($data, 'admin_password', substr(md5(mt_rand()), 0, $this->mMinPasswordLength)), 'Admin Password'),
                                    'db_type' => $this->GetParamValue($data, 'db_type', ''),
                                    'db_hostname' => $this->GetParamValue($data, 'db_hostname', ''),
                                    'db_username' => $this->GetParamValue($data, 'db_username', ''),
                                    'db_password' => $this->GetParamValue($data, 'db_password', ''),
                                    'db_name' => $this->GetParamValue($data, 'db_name', ''),
                                    'db_prefix' => $this->GetParamValue($data, 'db_prefix', ''),
									'daemonise' => $this->GetReminderDaemonSelection($this->GetParamValue($data, 'reminder_daemon', '0')),
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Administration setup for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->version,
                                       ),
                           'block' => array('body' => 'admin_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }


   public function DisplayCompletion()
   {
      // Trim the empty values in the $_POST array
      $data = array_filter($_POST, array($this, "TrimArgs"));

      $templates =
      array(
            'complete_body' => array(
                        'path' => TEMPLATE_FOLDER,
                        'template' => 'complete_install.tpl',
                        'vars' => array(
                                    'product_name' => $this->mProductName,
                                    'message' => $this->GetPageMessage(),
                                    'config_writeable' => $this->mWriteStatus['flyspray.conf.php'],
                                    'config_text' => $this->mConfigText,
                                    'admin_username' => $this->mAdminUsername,
                                    'admin_password' => $this->mAdminPassword,
                                    'site_index' => dirname($_SERVER['REQUEST_URI']) . '/../',
                                    'complete_action' => 'index.php',
                                    'daemonise' => true,
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Setup confirmation for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->version,
                                       ),
                           'block' => array('body' => 'complete_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }

   public function DisplayDatabaseSetup()
   {

      // Trim the empty values in the $_POST array
      $data = array_filter($_POST, array($this, "TrimArgs"));
      $this->CheckDatabaseSupport();

      // Make sure that the user can't choose a DB which is not supported
      foreach ($this->mSupportedDatabases as $db => $arr) {
        if (!$this->mAvailableDatabases[$db]['supported']) {
            unset($this->mSupportedDatabases[$db]);
        }
      }

      $templates =
      array(
            'database_body' => array(
                              'path' => TEMPLATE_FOLDER,
                              'template' => 'database.tpl',
                              'vars' => array(
                                          'product_name' => $this->mProductName,
                                          'message' => $this->GetPageMessage(),
                                          'databases' => $this->mSupportedDatabases,
                                          'db_type' => $this->GetParamValue($data, 'db_type', ''),
                                          'db_hostname' => $this->GetParamValue($data, 'db_hostname', 'localhost'),
                                          'db_username' => $this->GetParamValue($data, 'db_username', ''),
                                          'db_password' => $this->GetParamValue($data, 'db_password', ''),
                                          'db_name' => $this->GetParamValue($data, 'db_name', ''),
                                          'db_prefix' => $this->GetParamValue($data, 'db_prefix', 'flyspray_'),
                                          'version' => $this->version,
                                       ),
                           ),
            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Database setup for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->version,
                                       ),
                           'block' => array('body' => 'database_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }


   public function DisplayPreInstall()
   {
      // Check the Database support on the server.
      $this->CheckDatabaseSupport();

      $templates =
      array(
            'index_body' => array(
                        'path' => TEMPLATE_FOLDER,
                        'template' => 'pre_install.tpl',
                        'vars' => array(
                                    'product_name' => $this->mProductName,
                                    'required_php' => $this->mPhpRequired,
                                    'php_output' => $this->CheckPhpCompatibility(),
                                    'database_output' => $this->GetDatabaseOutput(),
                                    'config_output' => $this->CheckWriteability('flyspray.conf.php'),
                                    'cache_output' => $this->CheckWriteability('cache'),
                                    'att_output' => $this->CheckWriteability('attachments'),
                                    'config_status' => $this->mWriteStatus['flyspray.conf.php'],
                                    'xmlStatus' => $this->xmlStatus,
                                    'sapiStatus' => $this->sapiStatus,
                                    'php_settings' => $this->GetPhpSettings(),
                                    'status' => $this->CheckPreStatus(),
                                    'message' => $this->GetPageMessage(),
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Pre-Installation Check for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->version,
                                       ),
                           'block' => array('body' => 'index_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }

   public function DisplayLicense()
   {
      $templates =
      array(
            'license_body' => array(
                        'path' => TEMPLATE_FOLDER,
                        'template' => 'license.tpl',
                        'vars' => array(
                                    'product_name' => $this->mProductName,
                                    'message' => $this->GetPageMessage(),
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Licence Agreement for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->version,
                                       ),
                           'block' => array('body' => 'license_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }



   public function GetAdminInput($field, $value, $label)
   {
         $input_field	= "
         <tr>
            <td align=\"right\">$label</td>
            <td align=\"center\"><input class=\"inputbox\" type=\"text\" name=\"$field\" value=\"$value\" size=\"30\" /></td>
         </tr>";
      return $input_field;
   }


   public function GetDatabaseOutput()
   {
      $output = '';
      // Loop through the supported databases array
      foreach ($this->mSupportedDatabases as $which => $database)
      {
      $output .= "
      <tr>
         <td> - $which support</td>
         <td align=\"left\"><strong>{$this->mAvailableDatabases[$which]['status_output']}</strong></td>
         <td align=\"center\"><strong>". $this->ReturnStatus($this->mAvailableDatabases[$which]['supported'], $type = 'support')  . "</strong></td>
      </tr>";

      }
      // Return the html formatted results
      return $output;
   }



   /**
   * Function to get the php ini config values
   * @param string $option The ini setting name to check the status for
   * @return string The status of the setting either "On" or "OFF"
   */
   public function GetIniSetting($option)
   {
      return (ini_get($option) == '1' ? 'ON' : 'OFF');
   }

   /**
   * Function to get the error messages and generate an error output for the template
   * @string $heading The value for the Error message heading
   *                  The error message is stored in the $_SESSION Global array
   *                  $_SESSION[PASS_PHRASE]['page_message']. If there is no value in
   *                  this array, then there will be no error message outputed.
   * @return string $message The message which needs outputting
   */
   public function GetPageMessage()
   {
      // If there is an error
      if (isset($_SESSION['page_message']) || isset($_SESSION['page_heading']))
      {
         $message = '';
         if (isset($_SESSION['page_heading'])) {
            $message = '<h1 class="error">' . $_SESSION['page_heading'] . '</h1>';
         }

        if (isset($_SESSION['page_message'])) {
            // Get an html formated list
            $message .= '<div class="box"><div class="shade">' . $this->OutputHtmlList($_SESSION['page_message'],'ul') . '</div></div>';
        }


         // Destroy the session value
         unset($_SESSION['page_heading']);
         unset($_SESSION['page_message']);

         return $message;
      }
      else
      {
         return '';
      }
   }


   /**
   * Utility function to return a value from a named array or a specified default
   * @param array &$arr The array to get the values from
   * @param string $name The name of the key to check the value for
   * @param string $default The default value if the value is not set with the array
   * @return string $value The value to be returned
   */
   public function GetParamValue(&$arr, $name, $default=null )
   {
      $value = isset($arr[$name]) ? $arr[$name] : $default;
      return $value;
   }

   /**
   * Function to get a listing of recommended and actual settings
   * for php.
   * @param void
   * @return string $output HTML formatted string.
   */
   public function GetPhpSettings()
   {
      // Array of the setting name, php ini name and the recommended value
      $test_settings =
      array(
            array ('Safe Mode','safe_mode','OFF'),
            array ('File Uploads','file_uploads','ON'),
            array ('Magic Quotes GPC','magic_quotes_gpc','OFF'),
            array ('Register Globals','register_globals','OFF'),
            //array ('Output Buffering','output_buffering','OFF'),
            );

      if (substr(php_sapi_name(), 0, 3) == 'cgi') {
          $test_settings[] = array ('CGI fix pathinfo','cgi.fix_pathinfo','On');
      }

      $output = '';

      foreach ($test_settings as $recommended)
      {
      $actual_setting = $this->GetIniSetting($recommended[1]);

      $result = ($actual_setting == $recommended[2] )
         ?  '<span class="green"><strong>' . $recommended[2] . '</strong></span>'
         :  '<span class="red"><strong>' . $actual_setting . '</strong></span>';

      $output .=
      "
      <tr>
         <td>{$recommended[0]}</td><td align=\"center\"><strong>{$recommended[2]}</strong></td><td align=\"center\">{$result}</td>
      </tr>
      ";
      }
      return $output;
   }

    public function GetReminderDaemonSelection($value)
    {
        $selection	= '';

        if ($value == 1) {

                $selection .= '<input type="radio" name="reminder_daemon" value="1" checked="checked" /> Enable';
                $selection .= '<input type="radio" name="reminder_daemon" value="0" /> Disable';
        } else {

                $selection .= '<input type="radio" name="reminder_daemon" value="1" /> Enable';
                $selection .= '<input type="radio" name="reminder_daemon" value="0" checked="checked" /> Disable';
        }
            return $selection;

    }


   /**
   * Function to check if a particular folder/file is writeable.
   * @param string $fileSystem Path to check
   * $return boolean true/false
   */
   public function IsWriteable($fileSystem)
   {
      // Clear the cache
      clearstatcache();

      // Return the status of the permission
      return is_writable($fileSystem);
   }

    /**
   * Function to Output an Ordered/Un-ordered list from an array. Default list type is un-ordered.
   * @param array() $list_array An array list of data to be made into a list.
   * @return string $list An HTML list
   */
   public function OutputHtmlList($list_array = array(), $list_type = 'ul')
   {
      $list = "<$list_type>";
      foreach ($list_array as $list_item)
      {
         $list .= '<li>' . $list_item .'</li>';
      }
      $list .= "</$list_type>";

      return $list;
   }


   /**
   * Function to act on all the actions during Flyspray Setup
   * The Post variables are extracted for deciding which function to call.
   */
  public function ProcessActions()
   {
      $action = 'index';
      $what = '';

      extract($_POST);

      switch($action)
      {
         case 'licence':
            $this->DisplayLicense();
         break;

         case 'database':
            $this->DisplayDatabaseSetup();
         break;

         case 'administration':
            // Prepare the required data
            $required_data =
            array(
                  'db_hostname' => array('Database hostname', 'string', true),
                  'db_type' =>  array('Database type', 'string', true),
                  'db_username' => array('Database username', 'string', true),
                  'db_password' => array('Database password', 'string', false),
                  'db_name' => array('Database name', 'string', true),
                  'db_prefix' => array('Table prefix', 'string', false),
               );
            if ($data = $this->CheckPostedData($required_data, $message = 'Configuration Error'))
            {
               // Process the database checks and install tables
               if ($this->ProcessDatabaseSetup($data))
               {
                  // Proceed to Administration part
                  $this->DisplayAdministration();
               }
               else
               {
                  $_POST['action'] = 'database';
                  $this->DisplayDatabaseSetup();
               }
            }
            else
            {
               $_POST['action'] = 'database';
               $this->DisplayDatabaseSetup();
            }
         break;

         case 'complete':
            // Prepare the required data
            $required_data =
            array(
               'db_hostname' => array('Database hostname', 'string', true),
               'db_type' =>  array('Database type', 'string', true),
               'db_username' => array('Database username', 'string', true),
               'db_password' => array('Database password', 'string', false),
               'db_name' => array('Database name', 'string', true),
               'db_prefix' => array('Table prefix', 'string', false),
               'admin_username' => array('Administrator\'s username', 'string', true),
               'admin_password' => array("Administrator's Password must be minimum {$this->mMinPasswordLength} characters long and", 'password', true),
               'admin_email' => array('Administrator\'s email address', 'email address', true),
			   'reminder_daemon' => array('Reminder Daemon', 'option', false),
               );
            if ($data = $this->CheckPostedData($required_data, $message = 'Missing config values'))
            {
               // Set a page heading in case of errors.
               $_SESSION['page_heading'] = 'Administration Processing';

               if ($this->ProcessAdminConfig($data))
               {
                  $this->DisplayCompletion($data);
               }
               else
               {
                  $_POST['action'] = 'administration';
                  $this->DisplayAdministration();
               }
            }
            else
            {
               $_POST['action'] = 'administration';
               $this->DisplayAdministration();
            }
         break;

         default:
            $this->DisplayPreInstall();
         break;
      }
   }



   public function ProcessAdminConfig($data)
   {
      // Extract the varibales to local namespace
      extract($data);

      $config_intro	=
      "; <?php die( 'Do not access this page directly.' ); ?>

      ; This is the Flysplay configuration file. It contains the basic settings
      ; needed for Flyspray to operate. All other preferences are stored in the
      ; database itself and are managed directly within the Flyspray admin interface.
      ; You should consider putting this file somewhere that isn't accessible using
      ; a web browser, and editing header.php to point to wherever you put this file.\n";
      $config_intro	= str_replace("\t", "", $config_intro);

      // Create a random cookie salt
      $cookiesalt = md5(uniqid(mt_rand(), true));

	  // check to see if to enable the Reminder Daemon.
      $daemonise	= ( (isset($data['reminder_daemon'])) && ($data['reminder_daemon'] == 1) )
					? 1
					: 0;
      $db_prefix = (isset($data['db_prefix']) ? $data['db_prefix'] : '');

      $config	= array();
      $config[] = "[database]";
      $config[] = "dbtype = \"$db_type\"					; Type of database (\"mysql\", \"mysqli\" or \"pgsql\" are currently supported)";
      $config[] = "dbhost = \"$db_hostname\"				; Name or IP of your database server";
      $config[] = "dbname = \"$db_name\"					; The name of the database";
      $config[] = "dbuser = \"$db_username\"				; The user to access the database";
      $config[] = "dbpass = \"$db_password\"				; The password to go with that username above";
      $config[] = "dbprefix = \"$db_prefix\"				; The prefix to the {$this->mProductName} tables";
      $config[] = "\n";
      $config[] = '[general]';
      $config[] = "cookiesalt = \"$cookiesalt\"			; Randomisation value for cookie encoding";
      $config[] = 'output_buffering = "on"				; Available options: "on" or "gzip"';
      $config[] = "passwdcrypt = \"md5\"					; Available options: \"crypt\", \"md5\", \"sha1\" (Deprecated, do not change the default)";
      $config[] = "dot_path = \"\" ; Path to the dot executable (for graphs either dot_public or dot_path must be set)";
      $config[] = "dot_format = \"png\" ; \"png\" or \"svg\"";
      $config[] = "address_rewriting = \"0\"	; Boolean. 0 = off, 1 = on.";
      $config[] = "reminder_daemon = \"$daemonise\"		; Boolean. 0 = off, 1 = on (cron job), 2 = on (PHP).";
      $config[] = "doku_url = \"http://en.wikipedia.org/wiki/\"      ; URL to your external wiki for [[dokulinks]] in FS";
      $config[] = "syntax_plugin = \"none\"                               ; Plugin name for Flyspray's syntax (use any non-existing plugin name for deafult syntax)";
      $config[] = "update_check = \"1\"                               ; Boolean. 0 = off, 1 = on.";
      $config[] = "\n";
      $config[] = "[attachments]";
      $config[] = "zip = \"application/zip\" ; MIME-type for ZIP files";
      $config[] = "[oauth]";
      $config[] = 'enabled[] = ""';
      $config[] = ';enabled[] = "github"';
      $config[] = ';enabled[] = "google"';
      $config[] = ';enabled[] = "facebook"';
      $config[] = ';enabled[] = "microsoft"';
      $config[] = 'github_secret = ""';
      $config[] = 'github_id = ""';
      $config[] = 'github_redirect = "YOURDOMAIN/index.php?do=oauth&provider=github"';
      $config[] = 'google_secret = ""';
      $config[] = 'google_id = ""';
      $config[] = 'google_redirect = "YOURDOMAIN/index.php?do=oauth&provider=google"';
      $config[] = 'facebook_secret = ""';
      $config[] = 'facebook_id = ""';
      $config[] = 'facebook_redirect = "YOURDOMAIN/index.php?do=oauth&provider=facebook"';
      $config[] = 'microsoft_secret = ""';
      $config[] = 'microsot_id = ""';
      $config[] = 'microsoft_redirect = "YOURDOMAIN/index.php"';

      $config_text = $config_intro . implode( "\n", $config );

      if (is_writable('../flyspray.conf.php') && ($fp = fopen('../flyspray.conf.php', "wb")))
      {
         fputs($fp, $config_text, strlen($config_text));
         fclose($fp);
         $this->mWriteStatus['flyspray.conf.php'] = true;
      }
      else
      {
         $this->mConfigText = $config_text;
         $this->mWriteStatus['flyspray.conf.php'] = false;
      }


      // Setting the database for the ADODB connection
      require_once($this->mAdodbPath);

      $this->mDbConnection =& NewADOConnection(strtolower($db_type));
      $this->mDbConnection->Connect($db_hostname, $db_username, $db_password, $db_name);
      $this->mDbConnection->SetCharSet('utf8');

      // Get the users table name.
      $users_table	= (isset($db_prefix) ? $db_prefix : '') . 'users';

      $sql	= "SELECT * FROM $users_table WHERE user_id = '1'";

      // Check if we already have an Admin user.
      $result = $this->mDbConnection->Execute($sql);
      if ($result)
      {
         // If the record exists, we update it.
         $row = $result->FetchRow();
         $this->mAdminUsername = $row['user_name'];
         $this->mAdminPassword = $row['user_pass'];
      }

     $md5_password	= md5($admin_password);
     $update_user	= "
     UPDATE
        $users_table
     SET
        user_name = ?,
        user_pass = ?,
        email_address = ?
     WHERE
     user_id = '1'";

     $update_params = array($admin_username, $md5_password, $admin_email);

     $result = $this->mDbConnection->Execute($update_user, $update_params);

     if (!$result)
     {
        $errorno = $this->mDbConnection->MetaError();
        $_SESSION['page_heading'] = 'Failed to update Admin users details.';
        $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($errorno)) . ': '. $this->mDbConnection->ErrorMsg($errorno);
        return false;
     }
     else
     {
        $this->mAdminUsername = $admin_username;
        $this->mAdminPassword = $admin_password;
     }

      return true;
   }


   public function ProcessDatabaseSetup($data)
   {
      require_once($this->mAdodbPath);

      // Perform a number of fatality checks, then die gracefully
      if (!defined('_ADODB_LAYER'))
      {
         trigger_error('ADODB Libraries missing or not correct version');
      }

      // Setting the database type for the ADODB connection
      $this->mDbConnection =& NewADOConnection(strtolower($data['db_type']));

      /* check hostname/username/password */

      if (!$this->mDbConnection->Connect(array_get($data, 'db_hostname'), array_get($data, 'db_username'), array_get($data, 'db_password'), array_get($data, 'db_name')))
      {
         $_SESSION['page_heading'] = 'Database Processing';
         switch($error_number = $this->mDbConnection->MetaError())
         {
            case '-1':
            // We are using the unknown error code(-1) because ADOdb library may not have the error defined.
            // It could be totally some weird error.
            $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg();
            return false;
            break;

            case '-24':
            // Could not connect to database with the hostname provided
            $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . ': ' . ucfirst($this->mDbConnection->ErrorMsg($error_number));
            $_SESSION['page_message'][] = 'Usually the database host name is "localhost". In some occassions, it maybe an internal ip-address or another host name to your webserver.';
            $_SESSION['page_message'][] = 'Double check with your hosting provider or System Administrator.';
            return false;
            break;

            case '-25':
            // Database does not exist, try to create one
            $this->mDbConnection =& NewADOConnection(strtolower($data['db_type']));
            $this->mDbConnection->Connect(array_get($data, 'db_hostname'), array_get($data, 'db_username'), array_get($data, 'db_password'));
            $dict = NewDataDictionary($this->mDbConnection);
            $sqlarray = $dict->CreateDatabase(array_get($data, 'db_name'));
            if (!$dict->ExecuteSQLArray($sqlarray)) {
                $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . ': ' . ucfirst($this->mDbConnection->ErrorMsg($error_number));
                $_SESSION['page_message'][] = 'Your database does not exist and could not be created. Either create the database yourself, choose an existing database or
                                               use a database user with sufficient permissions to create a database.';
                return false;
            } else {
                $this->mDbConnection->SelectDB(array_get($data, 'db_name'));
                unset($_SESSION['page_heading']);
                break;
            }

            case '-26':
            // Username passwords don't match for the hostname provided
            $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . ': ' . ucfirst($this->mDbConnection->ErrorMsg($error_number));
            $_SESSION['page_message'][] = "Apparently you haven't set up the right permissions for the database hostname provided.";
            $_SESSION['page_message'][] = 'Double check the provided credentials or contact your System Administrator for further assistance.';
            return false;
            break;

            default:
            $_SESSION['page_message'][] = "Please verify your username/password/database details (error=$error_number)" . $this->mDbConnection->MetaErrorMsg($error_number);
            return false;
            break;
         }
      }
      // Check that table prefix is OK, some DBs don't like it
      $prefix = array_get($data, 'db_prefix');
      if (strlen($prefix) > 0 && is_numeric($prefix[0])) {
        $_SESSION['page_heading'] = 'Database Processing';
        $_SESSION['page_message'][] = 'The table prefix may not start with a number.';
        return false;
      }

       // Setting the Fetch mode of the database connection.
      $this->mDbConnection->SetFetchMode(ADODB_FETCH_BOTH);
      $this->mDbConnection->SetCharSet('utf8');
        //creating the datadict object for further operations
       $this->mDataDict = & NewDataDictionary($this->mDbConnection);

       include_once dirname($this->mAdodbPath) . '/adodb-xmlschema03.inc.php';

       $this->mXmlSchema =  new adoSchema($this->mDbConnection);

       // Populate the database with the new tables and return the result (boolean)
       if (!$this->PopulateDb($data))
       {
          return false;
       }

      return true;
   }

   /**
   * Function to populate the database with the sql constructs which is in an sql file
   * @param array $data The actual database configuration values
   * @return boolean
   */

   public function PopulateDb($data)
   {
      // Check available upgrade scripts, use the script of very latest  version
      $folders = glob_compat(BASEDIR . '/upgrade/[0-9]*');
      usort($folders, 'version_compare'); // start with lowest version
      $folders = array_reverse($folders); // start with highest version
      $sql_file	= APPLICATION_PATH . '/setup/upgrade/' . reset($folders) . '/flyspray-install.xml';

      $upgradeInfo = APPLICATION_PATH . '/setup/upgrade/' . reset($folders) . '/upgrade.info';
      $upgradeInfo = parse_ini_file($upgradeInfo, true);

       // Check if the install/upgrade file exists
      if (!is_readable($sql_file)) {

          $_SESSION['page_message'][] = 'SQL file required for importing structure and data is missing.';
          return false;
      }

       // Extract the variables to local namespace
       extract($data);
       if (!isset($db_prefix)) {
            $db_prefix = '';
       }

       if(is_numeric($db_prefix)) {
           $_SESSION['page_message'][] = 'database prefix cannot be numeric only';
           return false;
       }

        // Set the prefix for database objects ( before parsing)
      $this->mXmlSchema->setPrefix( (isset($db_prefix) ? $db_prefix : ''), false);
      $this->mXmlSchema->ParseSchema($sql_file);

      $this->mXmlSchema->ExecuteSchema();

      // Last but not least global prefs update
        if (isset($upgradeInfo['fsprefs'])) {
            $existing = $this->mDbConnection->GetCol("SELECT pref_name FROM {$db_prefix}prefs");
            // Add what is missing
            foreach ($upgradeInfo['fsprefs'] as $name => $value) {
                if (!in_array($name, $existing)) {
                    $this->mDbConnection->Execute("INSERT INTO {$db_prefix}prefs (pref_name, pref_value) VALUES (?, ?)", array($name, $value));
                }
            }
            // Delete what is too much
            foreach ($existing as $name) {
                if (!isset($upgradeInfo['fsprefs'][$name])) {
                    $this->mDbConnection->Execute("DELETE FROM {$db_prefix}prefs WHERE pref_name = ?", array($name));
                }
            }
        }

      $this->mDbConnection->Execute("UPDATE {$db_prefix}prefs SET pref_value = ? WHERE pref_name = 'fs_ver'", array($this->version));

      if (($error_no = $this->mDbConnection->MetaError()))
      {
         $_SESSION['page_heading'] = 'Database Processing';
         switch ($error_no)
         {
            case '-5':
            // If there are tables with the same name
            $_SESSION['page_message'][] = 'Table ' .$this->mDbConnection->MetaErrorMsg($this->mDbConnection->MetaError());
            $_SESSION['page_message'][] = 'There probably are tables in the database which have the same prefix you provided.';
            $_SESSION['page_message'][] = 'It is advised to change the prefix provided or you can drop the existing tables if you don\'t need them. Make a backup if you are not certain.';
            return false;
            break;

            case '-1':
            // We are using the unknown error code(-1) because ADOdb library may not have the error defined.
            $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg();
            return false;
            break;

            default:
            $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg() . ': ' . $this->mDbConnection->ErrorNo();
            $_SESSION['page_message'][] = 'Unknown error, please notify Developer quoting the error number';
            return false;
            break;
          }
       }

       return true;
   }




   /**
   * Function to return status of boolean results in html format
   * @param boolean $boolean The status of the result in True/False form
   * @param string $type The type of html format to return
   * @return string Depending on the type of format to return
   */
   public function ReturnStatus($boolean, $type = 'yes')
   {
      // Do a switch on the type of status
      switch($type)
      {
      case 'yes':
         return ($boolean)
         ?  '<span class="green">Yes</span>'
         :  '<span class="red">No</span>';
         break;

      case 'available':
         return ($boolean)
         ?  '<span class="green">Available</span>'
         :  '<span class="red">Missing</span>';
         break;

      case 'writeable':
         return ($boolean)
         ?  '<span class="green">Writeable</span>'
         :  '<span class="red">Un-writeable</span>';
         break;

      case 'on':
         return ($boolean)
         ?  '<span class="green">ON</span>'
         :  '<span class="red">OFF</span>';
         break;
      case 'support':
         return ($boolean)
         ?  '<span class="green">Supported</span>'
         :  '<span class="red">X</span>';
         break;
      default:
         return ($boolean)
         ?  '<span class="green">True</span>'
         :  '<span class="red">False</span>';
         break;
      }
   }

   /**
      * To verify if a string was empty or not.
      *
      * Usually used to validate user input. If the user has inputted empty data
      * or just blank spaces, we need to trim of such empty data and see if
      * anything else is left after trimming. If there is data remaining, then
      * the return value will be greater than 0 else it will be 0 (zero) which
      * equates to a true/false scenario
      *
      * @param string $arg The data to be checked
      *
      * @return The result of the check.
      */
   public function TrimArgs($arg)
   {
      return strlen(trim($arg));
   }

   public function VerifyVariableTypes($type, $value)
   {
      $message = '';
      switch($type)
      {
            case 'string':
                return is_string($value);
            break;

            case 'number':
                return is_numeric($value);
            break;

            case 'email address':
             return filter_var($value, \FILTER_VALIDATE_EMAIL);
             break;

            case 'boolean':
                return (bool) $value;
            break;

            case 'password':
                return (strlen($value) >= $this->mMinPasswordLength);
            break;

		    case 'folder':
		 	    return is_dir($value);
		    break;

            default:
                return true;
             break;
      }
   }

   /**
   * Function to output the templates
   * @param array $templates The collection of templates with their associated variables
   *
   */
   public function OutputPage($templates = array())
   {
      if (sizeof($templates) == 0)
      {
         trigger_error("Templates not configured properly", E_USER_ERROR);
      }

      // Define a set of common variables which plugin to the structure template.
      $page = new Tpl;
      $body = '';

      // Loop through the templates array to dynamically create objects and assign variables to them.
      /// XXX: this is not a common way to use our template class, but I didn't want to rewrite
      ///      the whole setup only to change the templating engine
      foreach($templates as $name => $module)
      {
        foreach ($module['vars'] as $var_name => $value) {
            $page->assign($var_name, $value);
        }

        if ($name == 'structure') {
            $page->assign('body', $body);
            $page->display('structure.tpl');
        } else {
            $body .= $page->fetch($module['template']);
        }
      }
   }
}

//start the installer, it handles the rest inside the class
new Setup();
