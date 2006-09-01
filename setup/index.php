<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2005 by Jeffery Fernandez <developer@jefferyfernandez.id.au>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//
// http://www.phpinfo.net/articles/article_php-coding-standard.html

session_start();

set_time_limit(0);

ini_set('memory_limit', '32M');

/*
if ( is_readable ('../flyspray.conf.php') && (count($config = parse_ini_file('../flyspray.conf.php', true)) > 0) )
{
   die('Flyspray Already Installed. Delete the contents of flyspray.conf.php to run setup.');
}
 */
// ---------------------------------------------------------------------
// Application information
// ---------------------------------------------------------------------
define('VALID_FLYSPRAY', 1 );
define('IN_FS', 1 );
define('APPLICATION_NAME', 'Flyspray');

// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
define('SERVER_WEB_ROOT', 'http://'.$_SERVER['SERVER_NAME']);
define('APPLICATION_SETUP_FOLDER', dirname($_SERVER['PHP_SELF']));
define('APPLICATION_SETUP_INDEX', SERVER_WEB_ROOT . APPLICATION_SETUP_FOLDER);
define('APPLICATION_WEB_ROOT', str_replace('setup',"",APPLICATION_SETUP_INDEX));

// ---------------------------------------------------------------------
// Application file system locations
// ---------------------------------------------------------------------
define('APPLICATION_PATH', dirname(dirname(__FILE__)));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes' );
define('BASEDIR', dirname(__FILE__));
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');
$conf['general']['syntax_plugin'] = '';

if (substr(PHP_OS, 0, 3) == 'WIN') {

  define('IS_MSWIN', true);

} else {

  define('IS_MSWIN', false);
}
require_once OBJECTS_PATH . '/fix.inc.php';
require_once(OBJECTS_PATH . '/class.flyspray.php');
require_once(OBJECTS_PATH . '/class.tpl.php');
require_once(OBJECTS_PATH . '/version.php');

class Setup extends Flyspray
{
   var $mPhpRequired;
   var $mSupportedDatabases;
   var $mAvailableDatabases;

   var $mProceed;
   var $mPhpVersionStatus;
   var $mDatabaseStatus;
   var $mConfigFileStatus;
   var $mConfigText;
   var $mHtaccessText;
   var $mCacheFolderStatus;

   var $mDbConnection;
   var $mProductName;
   var $mUnixName;
   var $mAuthor;

   /**
    * @var string To store the data filter type
    */
   var $mDataFilter;

   /**
    * @var array To store the type of database setup (install or Upgrade).
    */
   var $mDatabaseSetup;

   var $mPreferencesTable;
   var $mUsersTable;
   var $mAttachmentsTable;
   var $mCommentsTable;

   var $mServerSoftware;
   var $mMinPasswordLength;
   var $mAdminUsername;
   var $mAdminPassword;
   var $mCompleteAction;
   var $mPhpCliStatus;

   function Setup()
   {
      // Call parent constructor
      //$this->Flyspray();

      // Initialise Application values
      $mApplication				= & new Version();
      $this->mProductName	    = $mApplication->mProductName;
      $this->mVersion			= $mApplication->mVersion;
      $this->mCopyright			= $mApplication->mCopyright;
      $this->mUnixName			= $mApplication->mUnixName;
      $this->mAuthor			= $mApplication->mAuthor;
      // Look for ADOdb
      $this->mAdodbPath         = APPLICATION_PATH . '/adodb/adodb.inc.php';

      $this->mPreferencesTable	= 'flyspray_prefs';
      $this->mUsersTable		= 'flyspray_users';
      $this->mMinPasswordLength	= 8;

      // Initialise flag for proceeding to next step.
      $this->mProceed				= FALSE;
      $this->mPhpRequired			= '4.3';

      // If the database is supported in Flyspray, the function to check in PHP.
      $this->mSupportedDatabases	=
                           array(
                                 'MySQL' => array(TRUE, 'mysql_connect', 'mysql'),
                                 'MySQLi' => array(TRUE,'mysqli_connect','mysql'),
                                 'Postgres' => array(TRUE, 'pg_connect', 'pgsql'),
                              );
      $this->mAvailableDatabases	= array();

      // Array of information to setup the appropriate tables for installation
      // or upgrade of flyspray.
      $this->mDatabaseSetup		= array (
                                    1 => array ('Install 0.9.8' => '/sql/flyspray-0.9.8', 'dependency' => '', 'function' => 'InstallPointNineEight'),
                                    2 => array ('Upgrade 0.9.7 - 0.9.8' => '/sql/upgrade_0.9.7_to_0.9.8', 'dependency' => '3', 'function' => 'UpgradePointNineSeven'),
                                    // Only for testing3 => array ('Install 0.9.7' => '/sql/flyspray-0.9.7', 'dependency' => '', 'function' => 'InstallPointNineSeven'),
                                 );

      $this->mServerSoftware		= (strstr($_SERVER['SERVER_SOFTWARE'], 'Apache'))
                           ? 'apache'
                           : (
                              (strstr($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS'))
                              ? 'iis'
                              : 'unknown'
                             );

      // Process the page actions
      $this->ProcessActions();
   }

   /**
   * Function to scan include path and any additional paths for files
   * @param string $includeFile Name of file to scan for
   * @param string $additionalPath An additional path to scan for. eg Application path
   * @return boolean/string FALSE/actual path of file
   */
   function ScanIncludePath($includeFile, $additionalPath = '')
   {
      $where_path = '';

      // Add the optional application path to the include_path
      if (!empty($additionalPath))
      {
         ini_set('include_path', $additionalPath . PATH_SEPARATOR . ini_get('include_path'));
      }


      // Get the current include path settings from php config file
      $paths = explode(PATH_SEPARATOR, ini_get('include_path'));


      // Loop through the array of paths
      foreach($paths as $path)
      {
         // Check to see if the last character of the path is a "directory separator"
         if (substr($path, -1, 1) == DIRECTORY_SEPARATOR)
         {
            // If file exists, transfer the values and quit the foreach loop
            if (is_file($path . $includeFile))
            {
               $where_path = $path . $includeFile;
               break;
            }
         }
         else
         {
            // Include the "directory separator" in the scan
            if (is_readable($path . DIRECTORY_SEPARATOR . $includeFile))
            {
               $where_path = $path . DIRECTORY_SEPARATOR . $includeFile;
               break;
            }
         }
      }
      // Return success of scan
      return ($where_path) ? $where_path : FALSE;
   }

   /**
   * Function to check the permission of the config file
   * @param void
   * @return string An html formatted boolean answer
   */
   function CheckConfigFile()
   {
      // Get the full path to the file
      $file = APPLICATION_PATH .'/flyspray.conf.php';

      // Update the status of the Config file
      $this->mConfigFileStatus = $this->IsWriteable($file);

      // Return an html formated writeable/un-writeable string
      return $this->ReturnStatus($this->mConfigFileStatus, $type = 'writeable');
   }

   /**
   * Function to check the permission of the cache folder
   * @param void
   * @return string An html formatted boolean answer
   */
   function CheckCacheFolder()
   {
      // Get the full path to the file
      $file = APPLICATION_PATH . '/cache';

      // Update the status of the Cache folder
      $this->mCacheFolderStatus = $this->IsWriteable($file);

      // Return an html formated writeable/un-writeable string
      return $this->ReturnStatus($this->mCacheFolderStatus, $type = 'writeable');
   }


   /**
   * Function to check the availability of the Database support
   * @param void
   * @return void
   */
   function CheckDatabaseSupport()
   {
      $status = array();

      foreach ($this->mSupportedDatabases as $which => $database)
      {
      // Checking if the database has libraries built into PHP. Returns TRUE/FALSE
      $this->mAvailableDatabases[$which]['status'] = function_exists($database[1]);

      // If the Application(Flyspray) supports the available database supported in PHP
      $this->mAvailableDatabases[$which]['supported'] = ($database[0] === $this->mAvailableDatabases[$which]['status'])
         ?  $this->mAvailableDatabases[$which]['status']
         :  FALSE;

      // Just transferring the value for ease of veryfying Database support.
      $status[] = $this->mAvailableDatabases[$which]['supported'];

      // Generating the output to be displayed
      $this->mAvailableDatabases[$which]['status_output'] =
         $this->ReturnStatus($this->mAvailableDatabases[$which]['status'], $type = 'available');
      }

      //print_r($this->mAvailableDatabases);

      // Check if any one database support exists.
      // Update the status of database availability
      $this->mDatabaseStatus = (in_array('1', $status)) ? TRUE : FALSE;
   }


   function CheckPreStatus()
   {
      $this->mProceed =
      ($this->mDatabaseStatus && $this->mPhpVersionStatus)
      ?  TRUE
      :  FALSE;

      return $this->mProceed;
   }


   /**
   * Function to check the version of PHP available compared to the
   * Applications requirements
   * @param void
   * @return string An html formatted boolean answer
   */
   function CheckPhpCompatibility()
   {
      // Check the PHP version. Recommended version is 4.3 and above
      $this->mPhpVersionStatus = (phpversion() >= $this->mPhpRequired) ? TRUE : FALSE;

      // Return an html formated Yes/No string
      return $this->ReturnStatus($this->mPhpVersionStatus, $type = 'yes');
   }

   function CheckPhpCli()
   {
    // is executable doesn't exist in windows before PHP 5.0.0
   $executable_tester = (function_exists('is_executable')) ? 'is_executable' : 'is_file';

   $php_binary = 'php' . (IS_MSWIN ? '.exe' : '');

       /* Try to use PEAR::System __IF_AVAILABLE__(not an aditional
         requirement for flyspray)
         to _efectively_ locate the php binary */

        if($this->ScanIncludePath('System.php')) {

         include_once 'System.php';

         /* if found in the system PATH an is_executable..returns the php binary path
            if not, returns false */

         if( @System::which($php_binary) ) {

                $this->mPhpCliStatus = TRUE;
                return $this->mPhpCliStatus;

         }
            // see: http://php.net/reserved.constants
        }elseif($executable_tester( PHP_BINDIR . DIRECTORY_SEPARATOR . $php_binary)) {

			$this->mPhpCliStatus = TRUE;
			return $this->mPhpCliStatus;

        } else {

			return FALSE;
        }
   }

   /**
   * Function to check the posted data from forms.
   * @param array $expectedFields Array of field names which needs processing
   * If the array of filed names don't exist in the Posted data, then this function
   * will accumulate error messages in the $_SESSION[PASS_PHRASE]['page_message'] array.
   * return boolean/array $data will be returned if successful
   */
   function CheckPostedData($expectedFields = array(), $pageHeading)
   {

      // Grab the posted data and trim it.
      $data = array_filter($_POST, array($this, "TrimArgs"));


      // Loop through the required values and check data
      foreach($expectedFields as $key => $value)
      {

         // If the data is Required and is empty or not set
         if (!isset($data[$key]) || empty($data[$key]))
         {
            if ($expectedFields[$key][2] == TRUE)
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
         return FALSE;
      }
      else
      {
         return $data;
      }
   }

   function DisplayAdministration()
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
                                    'absolute_path' => realpath(APPLICATION_PATH),
                                    'admin_email' => $this->GetAdminInput('admin_email', $this->GetParamValue($data, 'admin_email', ''), 'Admin Email'),
                                    'pass_phrase' => $this->GetParamValue($data, 'pass_phrase', ''),
                                    'admin_username' => $this->GetAdminInput('admin_username', $this->GetParamValue($data, 'admin_username', ''), 'Admin Username'),
                                    'admin_password' => $this->GetAdminInput('admin_password', $this->GetParamValue($data, 'admin_password', $this->MakePassword($this->mMinPasswordLength)), 'Admin Password'),
                                    'db_type' => $this->GetParamValue($data, 'db_type', ''),
                                    'db_hostname' => $this->GetParamValue($data, 'db_hostname', ''),
                                    'db_username' => $this->GetParamValue($data, 'db_username', ''),
                                    'db_password' => $this->GetParamValue($data, 'db_password', ''),
                                    'db_name' => $this->GetParamValue($data, 'db_name', ''),
                                    'db_prefix' => $this->GetParamValue($data, 'db_prefix', ''),
                                    'db_setup_options' => $this->GetParamValue($data, 'db_setup_options', ''),
									'daemonise' => $this->GetReminderDaemonSelection($this->GetParamValue($data, 'reminder_daemon', '1')),
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Administration setup for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->mVersion,
                                       'copyright' => $this->mCopyright,
                                       ),
                           'block' => array('body' => 'admin_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }


   function DisplayCompletion()
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
                                    'config_writeable' => $this->mConfigFileStatus,
                                    'config_text' => $this->mConfigText,
                                    'admin_username' => $this->mAdminUsername,
                                    'admin_password' => $this->mAdminPassword,
                                    'site_index' => dirname($_SERVER['REQUEST_URI']) . '/../',
                                    'complete_action' => $this->mCompleteAction,
                                    'daemonise' => $this->CheckPhpCli(),
                                 ),
                     ),

            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Setup confirmation for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->mVersion,
                                       'copyright' => $this->mCopyright,
                                       ),
                           'block' => array('body' => 'complete_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }

   function DisplayDatabaseSetup()
   {

      // Trim the empty values in the $_POST array
      $data = array_filter($_POST, array($this, "TrimArgs"));

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
                                          'db_name' => $this->GetParamValue($data, 'db_name', $this->mUnixName),
                                          'db_prefix' => $this->GetParamValue($data, 'db_prefix', 'flyspray_'),
                                          'db_delete' => (isset($data['db_delete'])) ? 1 : 0,
                                          'db_backup' => (isset($data['db_backup'])) ? 1 : 0,
                                          'db_setup_options' => $this->GetSetupOptions()
                                       ),
                           ),
            'structure' =>  array(
                           'path' => TEMPLATE_FOLDER,
                           'template' => 'structure.tpl',
                           'vars' => array(
                                       'title' => 'Database setup for',
                                       'headers' => '',
                                       'index' => APPLICATION_SETUP_INDEX,
                                       'version' => $this->mVersion,
                                       'copyright' => $this->mCopyright,
                                       ),
                           'block' => array('body' => 'database_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }

   /**
   * Function to prepare template output
   *
   *
   */
   function DisplayPreInstall()
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
                                    'config_output' => $this->CheckConfigFile(),
                                    'config_status' => $this->mConfigFileStatus,
                                    //'cache_output' => $this->CheckCacheFolder(),
                                    //'cache_status' => $this->mCacheFolderStatus,
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
                                       'version' => $this->mVersion,
                                       'copyright' => $this->mCopyright,
                                       ),
                           'block' => array('body' => 'index_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }

   function DisplayLicense()
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
                                       'version' => $this->mVersion,
                                       'copyright' => $this->mCopyright,
                                       ),
                           'block' => array('body' => 'license_body')
                           )
         );

      // Output the final template.
      $this->OutputPage($templates);
   }



   function GetAdminInput($field, $value, $label)
   {
      $input_field = '';
      // If its a fresh install show the admin input fields
      if ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')
      {
         $input_field	= "
         <tr>
            <td align=\"right\">$label</td>
            <td align=\"center\"><input class=\"inputbox\" type=\"text\" name=\"$field\" value=\"$value\" size=\"30\" /></td>
         </tr>";
      }
      return $input_field;
   }


   function GetDatabaseOutput()
   {
      //print_r($this->mAvailableDatabases);

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
   function GetIniSetting($option)
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
   function GetPageMessage()
   {
      // If there is an error
      if (isset($_SESSION['page_message']) && isset($_SESSION['page_heading']))
      {
         // Get an html formated list
         $page_message = $this->OutputHtmlList($_SESSION['page_message'],'ul');

         $message =
         '<h1 class="error">' . $_SESSION['page_heading'] . '</h1>
         <div class="box">
         <div class="shade">'.
            $page_message . '
         </div>
         </div>';

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
   function GetParamValue(&$arr, $name, $default=null )
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
   function GetPhpSettings()
   {
      // Array of the setting name, php ini name and the recommended value
      $test_settings =
      array(
            array ('Safe Mode','safe_mode','OFF'),
            array ('File Uploads','file_uploads','ON'),
            //array ('Magic Quotes GPC','magic_quotes_gpc','OFF'),
            array ('Register Globals','register_globals','OFF'),
            //array ('Output Buffering','output_buffering','OFF'),
            );

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


	function GetReminderDaemonSelection($value)
	{
		$selection	= '';
		if ($this->CheckPhpCli())
		{
			if ($value == 1)
			{
				$selection .= '<input type="radio" name="reminder_daemon" value="1" checked="checked" /> Enable';
				$selection .= '<input type="radio" name="reminder_daemon" value="0" /> Disable';
			}
			else
			{
				$selection .= '<input type="radio" name="reminder_daemon" value="1" /> Enable';
				$selection .= '<input type="radio" name="reminder_daemon" value="0" checked="checked" /> Disable';
			}
			return $selection;
		}
		else
		{
			return FALSE;
		}
	}


   function GetSetupOptions()
   {
      //print_r($this->mDatabaseSetup);
      $setup_array	= array();
      $setup_procedure = '<select name="db_setup_options">';
      foreach ($this->mDatabaseSetup as $key_array => $key_data)
      {
         foreach ($key_data as $install_type => $details)
         {
            //echo $key_array."<br />";
            //$setup_array[]		= $install_type;
            $selected			= ( (isset($_POST['db_setup_options'])) && ($_POST['db_setup_options'] == $key_array) )
                           ? 'selected="selected"'
                           : '';
            $setup_procedure	.= "<option value=\"$key_array\" $selected>$install_type</option>";

            break;
         }
      }
      $setup_procedure .= '</select>';
      return $setup_procedure;
   }

   function InstallPointNineEight($data)
   {
      return TRUE;
   }

   function InstallPointNineSeven($data)
   {
      return TRUE;
   }




   /**
   * Function to check if a particular folder/file is writeable.
   * @param string $fileSystem Path to check
   * $return boolean TRUE/FALSE
   */
   function IsWriteable($fileSystem)
   {
      // Clear the cache
      clearstatcache();

      // Return the status of the permission
      return (is_writable($fileSystem))
      ? TRUE
      : FALSE;
   }

   function MakePassword($passwordLength)
   {
      $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $length = strlen($salt);
      $password = '';
      mt_srand(10000000*(double)microtime());

      for ($i = 0; $i < $passwordLength; $i++)
      {
         $password .= $salt[mt_rand(0, $length - 1)];
      }

      return $password;
   }


   /**
   * Function to Output an Ordered/Un-ordered list from an array. Default list type is un-ordered.
   * @param array() $list_array An array list of data to be made into a list.
   * @return string $list An HTML list
   */
   function OutputHtmlList($list_array = array(), $list_type = 'ul')
   {
      $list = "<$list_type>";
      foreach ($list_array as $list_item)
      {
         $list .= "<li>$list_item</li>";
      }
      $list .= "</$list_type>";

      return $list;
   }


   /**
   * Function to act on all the actions during Flyspray Setup
   * The Post variables are extracted for deciding which function to call.
   */
   function ProcessActions()
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
            // Prepare the required data
            $required_data =
            array(
                  'agreecheck' => array(
                                 'Licence Agreement', 'string', TRUE
                                 )
               );

            if ($this->CheckPostedData($required_data, $message = 'Accept Licence'))
            {
               $this->DisplayDatabaseSetup();
            }
            else
            {
               $_POST['action'] = 'licence';
               $this->DisplayLicense();
            }
         break;

         case 'administration':
            // Prepare the required data
            $required_data =
            array(
                  'db_hostname' => array('Database hostname', 'string', TRUE),
                  'db_type' =>  array('Database type', 'string', TRUE),
                  'db_username' => array('Database username', 'string', FALSE),
                  'db_password' => array('Database password', 'string', FALSE),
                  'db_name' => array('Database name', 'string', TRUE),
                  'db_prefix' => array('Table prefix', 'string', TRUE),
                  'db_delete' => array('Delete tables checkbox', 'string', FALSE),
                  'db_backup' => array('Database backup checkbox', 'string', FALSE),
                  'db_setup_options' => array('Database Setup Options', 'number', TRUE)
               );
            if ($data = $this->CheckPostedData($required_data, $message = 'Configuration Error'))
            {
               // Set a page heading in case of errors.
               $_SESSION['page_heading'] = 'Database Processing';
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
               'db_hostname' => array('Database hostname', 'string', TRUE),
               'db_type' =>  array('Database type', 'string', TRUE),
               'db_username' => array('Database username', 'string', FALSE),
               'db_password' => array('Database password', 'string', FALSE),
               'db_name' => array('Database name', 'string', TRUE),
               'db_prefix' => array('Table prefix', 'string', TRUE),
               'db_setup_options' =>  array('Database type', 'number', TRUE),
               'absolute_path' => array($this->mProductName . ' Absolute path must exist and', 'folder', TRUE),
               'admin_username' => array('Administrator\'s username', 'string', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
               'admin_password' => array("Administrator's Password must be minimum {$this->mMinPasswordLength} characters long and", 'password', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
               'admin_email' => array('Administrator\'s email address', 'email address', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
			   'reminder_daemon' => array('Reminder Daemon', 'option', FALSE),
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



   function ProcessAdminConfig($data)
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
      $cookiesalt = substr(md5(microtime()), 0, 2);

	  // Check to see if running apache software and mod_rewrite is enabled (Can only check in PHP 4.3.2 and above)
	  $re_writing	= 0;
	  if ($this->mServerSoftware == 'apache' && function_exists('apache_get_modules'))
	  {
			$apache_modules	= apache_get_modules();
			if ( ($apache_modules) && (in_array('mod_rewrite', $apache_modules)) )
			{
				$re_writing = 1;
			}
	  }

	  // check to see if to enable the Reminder Daemon.
      $daemonise	= ( (isset($data['reminder_daemon'])) && ($data['reminder_daemon'] == 1) )
					? 1
					: 0;

	  // Double check the urls and paths slashes.

      $config	= array();
      $config[] = "[database]";
      $config[] = "dbtype = \"$db_type\"					; Type of database (\"mysql\" or \"pgsql\" are currently supported)";
      $config[] = "dbhost = \"$db_hostname\"				; Name or IP of your database server";
      $config[] = "dbname = \"$db_name\"					; The name of the database";
      $config[] = "dbuser = \"$db_username\"				; The user to access the database";
      $config[] = "dbpass = \"$db_password\"				; The password to go with that username above";
      $config[] = "dbprefix = \"$db_prefix\"				; The prefix to the {$this->mProductName} tables";
      $config[] = "\n";
      $config[] = '[general]';
      $config[] = "cookiesalt = \"$cookiesalt\"			; Randomisation value for cookie encoding";
      $config[] = "adodbpath = \"{$this->mAdodbPath}\"	; Path to the main ADODB include file";
      $config[] = 'output_buffering = "on"				; Available options: "on" or "gzip"';
      $config[] = "passwdcrypt = \"md5\"					; Available options: \"crypt\", \"md5\", \"sha1\"";
      $config[] = "address_rewriting = \"$re_writing\"	; Boolean. 0 = off, 1 = on.";
      $config[] = "reminder_daemon = \"$daemonise\"		; Boolean. 0 = off, 1 = on.";
      $config[] = "doku_url = \"http://en.wikipedia.org/wiki/\"      ; URL to your external wiki for [[dokulinks]] in FS";
      $config[] = "syntax_plugin = \"none\"                               ; Plugin name for Flyspray's syntax (use any non-existing plugin name for deafult syntax)";
      $config[] = "update_check = \"1\"                               ; Boolean. 0 = off, 1 = on.";
      $config[] = "\n";
      $config[] = "[attachments]";
      $config[] = "zip = \"application/zip\" ; MIME-type for ZIP files";

      $config_text = $config_intro . implode( "\n", $config );

      if (is_writable('../flyspray.conf.php') && ($fp = fopen('../flyspray.conf.php', "wb")))
      {
         fputs($fp, $config_text, strlen($config_text));
         fclose($fp);
         $this->mConfigFileStatus = TRUE;
      }
      else
      {
         $this->mConfigText = $config_text;
         $this->mConfigFileStatus = FALSE;
      }


      // Setting the database for the ADODB connection
      require_once($this->mAdodbPath);
      $this->mDbConnection =& NewADOConnection(strtolower($db_type));
      $this->mDbConnection->Connect($db_hostname, $db_username, $db_password, $db_name);

      // Get the users table name.
      $users_table	= ($db_prefix != '')
                  ? str_replace("{$this->mUnixName}_", $db_prefix, $this->mUsersTable )
                  : $this->mUsersTable;
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


      // If the admin inputs have been posted.. Only for fresh install
      if (isset($admin_username) && isset($admin_password) && isset($admin_email))
      {
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
            $_SESSION['page_heading'][]	= 'Failed to update Admin users details.';
            return FALSE;
         }
         else
         {
            $this->mAdminUsername = $admin_username;
            $this->mAdminPassword = $admin_password;
         }
         $this->mCompleteAction	= 'do=authenticate';
      }
      else
      {
         $this->mAdminUsername = '';
         $this->mAdminPassword = '';
         $this->mCompleteAction	= 'do=myprofile';
         $this->SetUpgradeLogin($data, $cookiesalt);
      }
      return TRUE;
   }


   function ProcessDatabaseSetup($data)
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

      if (!$this->mDbConnection->Connect($data['db_hostname'], $data['db_username'], $data['db_password'], $data['db_name']))
      {
         switch($error_number = $this->mDbConnection->MetaError())
         {
            case '-1':
            // We are using the unknown error code(-1) because ADOdb library may not have the error defined.
            // It could be totally some weird error.
            $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg();
            return FALSE;
            break;

            case '-24':
            // Could not connect to database with the hostname provided
            $_SESSION['page_message'][] = 'Database ' . ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
            $_SESSION['page_message'][] = 'Usually the database host name is "localhost". In some occassions, it maybe an internal ip-address or another host name to your webserver.';
            $_SESSION['page_message'][] = 'Double check with your hosting provider or System Administrator.';
            return FALSE;
            break;

            case '-26':
            // Username passwords don't match for the hostname provided
            $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
            $_SESSION['page_message'][] = "Obviously you haven't set up the right permissions for the database hostname provided.";
            $_SESSION['page_message'][] = 'Double check the provided credentials or contact your System Administrator for further assistance.';
            return FALSE;
            break;

            default:
            $_SESSION['page_message'][] = "Please verify your username/password/database details (error=$error_number)";
            return FALSE;
            break;
         }
      }
      else
      {
           // Setting the Fetch mode of the database connection.
           $this->mDbConnection->SetFetchMode(ADODB_FETCH_BOTH);

           // Backup and delete tables if requested
           if ($this->BackupDeleteTables($data))
           {
              if ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')
              {
                 // Populate the database with the new tables and return the result (boolean)
                 if (!$this->PopulateDb($data))
                 {
                    return FALSE;
                 }
              }
              else
              {
                 // Call the dependency function
                 if (method_exists($this, $this->mDatabaseSetup[$_POST['db_setup_options']]['function']))
                 {
                    // Call the Upgrade function.
                    if (!$this->{$this->mDatabaseSetup[$_POST['db_setup_options']]['function']}($data))
                    {
                       return FALSE;
                    }
                 }
                 else
                 {
                    $_SESSION['page_message'][]	= "Function {$this->mDatabaseSetup[$_POST['db_setup_options']]['function']}() not defined!";
                    return FALSE;
                 }
              }
           }
           else
           {
              return FALSE;
           }
      }
      return TRUE;
   }


   function BackupTables($data, $table_list)
   {
      // Extract the data to local namespace
      extract($data);

      //Get the date value to rename tables with the date+time prefix if the backup option was ticked
      $date_time = date("YmdHis");
      $db_bu_prefix  = $date_time . '_';

      // Loop through the tables array
      foreach ($table_list as $table)
      {
         // Giving the backup table a new prefix based on the date & time of action
         $bu_table = $db_bu_prefix . $table;

         // Query to copy the existing table into a table with the new prefix
         switch (strtolower($db_type))
         {
            case 'mysql':
               $sql	= "CREATE TABLE $bu_table AS SELECT * FROM $table";
            break;

            case 'postgres':
               $sql	= "CREATE TABLE \"$bu_table\" AS SELECT * FROM $table";
            break;
         }

         $this->mDbConnection->Execute($sql);

         // If any errors, record the error message in the array
         if ($error_number = $this->mDbConnection->MetaError())
         {
            $_SESSION['page_message'][] =  ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . ' Table backup error: ' . $sql;
         }
      }
      // Check for any error messages.
      if (isset($_SESSION['page_message']) && count($_SESSION['page_message']) > 0)
      {
         return FALSE;
      }
      else
      {
         return TRUE;
      }
   }

   function DeleteTables($data, $table_list)
   {
      extract($data);

      // Loop through the tables array
      foreach ($table_list as $table)
      {
         $db_type	= strtolower($db_type);
         // Prepare the query to drop the existing tables
	 switch ($db_type)
	 {
	   case 'mysql':
	     $sql = "DROP TABLE $table";
	     break;
	   case 'postgres':
	     $sql = "DROP TABLE $table cascade";
	     break;
	 }
         $this->mDbConnection->Execute($sql);

         // If any errors, record the error message in the array
         if ($error_number = $this->mDbConnection->MetaError())
         {
            $_SESSION['page_message'][] =  ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . " Error deleting: $sql";
            return FALSE;
         }
      }
      return TRUE;
   }


   function DeleteSequences($data)
   {
      extract($data);
      $db_type	= strtolower($db_type);

      if ($db_type != 'postgres') {
	return TRUE;
      }
      $sql = "SELECT c.relname
	FROM pg_catalog.pg_class c
	LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
	LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
	WHERE c.relkind = 'S'
	AND n.nspname NOT IN ('pg_catalog', 'pg_toast')
	AND pg_catalog.pg_table_is_visible(c.oid)";

      $result = $this->mDbConnection->Execute($sql);
      $sequence_list = array();
      if ($result)
      {
	while ($row = $result->FetchRow())
	{
	  $sequence_list[] = $row[0];
	}
      }

      foreach ($sequence_list as $sequence) {
	$sql = "DROP SEQUENCE $sequence";
	$this->mDbConnection->Execute($sql);
	// If any errors, record the error message in the array
	if ($error_number = $this->mDbConnection->MetaError())
	{
	  $_SESSION['page_message'][] =  ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
	  return FALSE;
	}
      }

      return TRUE;
   }


   function BackupDeleteTables($data)
   {
      // Extract the data to local namespace
      extract($data);

      if ((isset($db_delete)) || (isset($db_backup)))
      {
         $db_type	= strtolower($db_type);
         // Query to get a list of tables in the database
         switch ($db_type)
         {
            case 'mysql':
                $sql	= "SHOW TABLES FROM $db_name";
                $params = false;
            break;

            case 'postgres':
                $sql	= 'SELECT tablename from pg_tables where tableowner = ?';
                $params = array($db_username);
            break;
         }

         $result = $this->mDbConnection->Execute($sql, $params);

         $table_list = array();
         if ($result)
         {
            while ($row = $result->FetchRow())
            {
               $table_list[] = $row[0];
            }
         }

         // Filter the tables we want to work with ..... relating it to the table prefix.
         // If the prefix given is the same as the prefix we have in the database
         // If the prefix does not match.. we can't delete or backup the intended tables
         $filtered_tables	= array();
         foreach ($table_list as $table)
         {
            if (strpos($table, $db_prefix) === 0)
            {
               $filtered_tables[]	= $table;
            }
         }

         // If it was requested to delete existing tables.
         if (isset($db_delete))
         {
            // If we are deleting, backup NEEDS to be done.
            if ((isset($db_delete)) && (isset($db_backup)))
            {
               if (!count($filtered_tables) > 0)
               {
                  // If there were no tables at all in the database.
                  $_SESSION['page_message'][] =  'Tables with provided prefix not found in database.';
                  $_SESSION['page_message'][] =  'Make sure you have the right table prefix for the database tables you intend to backup/delete';
                  $_SESSION['page_message'][] =  'You are safe to proceed into the setup without dropping tables as tables with the provided prefix don\'t exist!';
                  return FALSE;
               }

               // Drop/backup tables only if there are no dependancies. eg. Fresh Install
               if ( ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == ''))
               {
                  // Return error if Backup was not successful.
                  if (!$this->BackupTables($data, $filtered_tables))
                  {
                     return FALSE;
                  }
               }
               else
               {
                  $_SESSION['page_message'][] =  'Tables where not dropped because you are performing an UPGRADE.';
                  $_SESSION['page_message'][] =  'Unckeck the "delete" checkbox to proceed into the setup.';
                  return FALSE;
               }


               // Drop tables only if there are no dependancies. eg. Fresh Install
	        if ( ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == ''))
               {
                  // Return the status of deleting the tables if it was not successful.
		 if (!$this->DeleteTables($data, $filtered_tables))
		 {
		   return FALSE;
		 }
		 if (!$this->DeleteSequences($data)) {
		   return FALSE;
		 }
               }
               else
               {
                  $_SESSION['page_message'][] =  'Tables where not dropped because you are performing an UPGRADE.';
                  $_SESSION['page_message'][] =  'Unckeck the "delete backup" checkbox to proceed into the setup.';
                  return FALSE;
               }
            }
            else
            {
               // Not dropping the tables unless the backup option is checked.
               $_SESSION['page_message'][] =  'Please select the "backup tables" checkbox in order to drop existing tables.';
               return FALSE;
            }
            return TRUE;
         }


         // Perform actions if only the backup was selected.
         if (isset($db_backup))
         {
            if (!count($filtered_tables) > 0)
            {
               // If there were no tables at all in the database.
               $_SESSION['page_message'][] =  'Tables with provided prefix not found in database.';
               $_SESSION['page_message'][] =  'Make sure you have the right table prefix for the database tables you intend to backup';
               $_SESSION['page_message'][] =  'It is safe to uncheck the "backup tables"';
               return FALSE;
            }

            // Return error if Backup was not successful.
            if (!$this->BackupTables($data, $filtered_tables))
            {
               return FALSE;
            }
         }
      }
      return TRUE;
   }

   /**
   * Function to populate the database with the sql constructs which is in an sql file
   * @param array $data The actual database configuration values
   * @return boolean
   */
   function PopulateDb($data)
   {
      // Get the sql file name and set the path
      list($file) = array_values($this->mDatabaseSetup[$_POST['db_setup_options']]);

      $sql_file	= APPLICATION_PATH . "$file." . $this->mSupportedDatabases[$_POST['db_type']][2];

      // Check if the install/upgrade file exists
      if (is_readable($sql_file))
      {
         // Extract the variables to local namespace
         extract($data);

         // Disable magic quotes runtime. Some bytes in binary files may be interpreted as
         // \ (backslash), " (double quotes), ' (simple quote) or any "special" character
         // that has a meaning for string processing.
         $query = fread(fopen($sql_file, "rb"), filesize($sql_file));


         // Get the sql queries
         $sql_blocks  = $this->SplitSql($query, $db_type);

         $sql_count = count($sql_blocks);
         for ($i=0; $i < $sql_count; $i++)
         {
            $sql_blocks[$i] = trim($sql_blocks[$i]);
            if(!empty($sql_blocks[$i]) && $sql_blocks[$i] != "#")
            {
               $sql_blocks[$i] = str_replace($this->mUnixName . "_", $db_prefix, $sql_blocks[$i]);

               $this->mDbConnection->Execute($sql_blocks[$i]);

               if (($error_no = $this->mDbConnection->MetaError()))
               {
                  switch ($error_no)
                  {
                     case '-5':
                     // If there are tables with the same name
                     $_SESSION['page_message'][] = 'Table ' .$this->mDbConnection->MetaErrorMsg($this->mDbConnection->MetaError());
                     $_SESSION['page_message'][] = 'There probably are tables in the database which have the same prefix you provided.';
                     $_SESSION['page_message'][] = 'It is advised to change the prefix provided or you can drop the existing tables if you don\'t need them. Make a backup if you are not certain.';
                     return FALSE;
                     break;

                     case '-1':
                     // We are using the unknown error code(-1) because ADOdb library may not have the error defined.
                     $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg();
                     return FALSE;
                     break;

                     default:
                     $_SESSION['page_message'][] = $this->mDbConnection->ErrorMsg() . ': ' . $this->mDbConnection->ErrorNo();
                     $_SESSION['page_message'][] = 'Unknown error, please notify Developer quoting the error number';
                     return FALSE;
                     break;
                  }
               }
            }
         }
         //$_SESSION['page_message'][] = 'Successfully populated database with structure and data.';
         return TRUE;
      }
      else
      {
         $_SESSION['page_message'][] = 'SQL file required for importing structure and data is missing.';
         return FALSE;
      }
   }



   /**
   * Function to return status of boolean results in html format
   * @param boolean $boolean The status of the result in True/False form
   * @param string $type The type of html format to return
   * @return string Depending on the type of format to return
   */
   function ReturnStatus($boolean, $type = 'yes')
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

   function SetUpgradeLogin($data, $cookiesalt)
   {
      // Extract the varibales to local namespace
      extract($data);

      // The user should be remembered on this machine
      $cookie_time = time() + (60 * 60 * 24 * 30); // Set cookies for 30 days

      // Get current user details. Assuming its always user_id 1
      $result		= $this->mDbConnection->Query("SELECT * FROM {$db_prefix}users WHERE user_id = ?", array(1));
      $user		= $this->mDbConnection->Execute($result);

      // Get current user details.  We need this to see if their account is enabled or disabled
      $result = $this->mDbConnection->Execute("SELECT * FROM {$db_prefix}users WHERE user_id = ?", array(1));
      $user	= $result->FetchRow();

      // Set a couple of cookies
      setcookie('flyspray_userid', $user['user_id'], $cookie_time, "/");
      setcookie('flyspray_passhash', crypt($user['user_pass'], $cookiesalt), $cookie_time, "/");

      // If the user had previously requested a password change, remove the magic url
      $remove_magic = $this->mDbConnection->Query("UPDATE {$db_prefix}users SET
                           magic_url = ''
                           WHERE user_id = ?",
                           array($user['user_id'])
                        );

      $_SESSION['SUCCESS'] = 'Login successful.';
   }


   /**
   * Function to split the SQL queries from a SQL file into individual queries
   * Thanks to Ben Balbo http://www.benbalbo.com for the code comments
   * @param string $sql The sql queries which was grabbed from a SQL file
   */
   function SplitSql($sql, $db_type)
   {
      // Trim the SQL
      $sql = trim($sql);
	  switch (strtolower($db_type))
	  {

      	// Removes any lines that start with a # and --
      	//$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql); // Doesn't work as expected
		case 'mysql':
			$sql = ereg_replace("#[^\n]*\n", "\n", $sql);
		break;

		case 'postgres':
			$sql = ereg_replace("--[^\n]*\n", "\n", $sql);
		break;
	  }
      // This array only ever has 2 items [0] is previous character seen - [1] is current character
      $buffer = array();

      // Array for returning the SQL arrays
      $sql_lists = array();

      // Flag for defining whether we're parsing the innards of a string (i.e. we passed a " or ' recently
      $in_string = FALSE;

      // Loop through each character in the sql string
      for($i = 0; $i < strlen($sql)-1; $i++)
      {

         // If the character is equal to a semi colon and the flag is false
         // this is tru if the current char is a semicolon and it's not the first char in the string (i.e. $in_string contains something)
         if($sql[$i] == ";" && !$in_string)
         {
            // Get the characters from the beginning of the sql string up until the semi-colon
            $sql_lists[] = substr($sql, 0, $i);

            // Re-set the sql string to start from the current character and the rest of the string
            $sql = substr($sql, $i + 1);
            // Re-set the counter
            $i = 0;
         }

         // If we're flagged as being in a string (between '' or "") and we've just found what looks like a string terminator
         // check to see if previous char was not a backslash, and if so, we're not in a string anymore.
         // In other words, if we recently found a " and we've just found another ", we can consider ourselves not
         // in the string anymore, unless the previous char was a backslash which would escape the " we just found.
         if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
         {
            $in_string = false;
         }
         elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\"))
         // Catches the start of the string - if we've found a ' or " and the previous char
         // wasn't a \ and we're not in a string, then we are now!
         {
            $in_string = $sql[$i];
         }

         // Scroll buffer. [0] is now equal to [1] if it had a value. current char is now in [1]
         if(isset($buffer[1]))
         {
            $buffer[0] = $buffer[1];
         }
         $buffer[1] = $sql[$i];
      }

      if(!empty($sql))
      {
         $sql_lists[] = $sql;
      }
      return $sql_lists;
   }

   /**
      * To verify if a string was empty or not.
      *
      * Usually used to validate user input. If the user has inputted empty data
      * or just blank spaces, we need to trim of such empty data and see if
      * anything else is left after trimming. If there is data remaining, then
      * the return value will be greater than 0 else it will be 0 (zero) which
      * equates to a TRUE/FALSE scenario
      *
      * @param string $arg The data to be checked
      *
      * @return The result of the check.
      */
   function TrimArgs($arg)
   {
      return strlen(trim($arg));
   }

   function UpgradePointNineSeven($data)
   {
      // Extract the data to local namespace
      extract($data);

      // Get the preferences table name.
      $preferences_table	= ($db_prefix != '')
                     ? str_replace("{$this->mUnixName}_", $db_prefix, $this->mPreferencesTable)
                     : $this->mPreferencesTable;

      $attachments_table	= ($db_prefix != '')
                     ? str_replace("{$this->mUnixName}_", $db_prefix, $this->mAttachmentsTable)
                     : $this->mAttachmentsTable;

      $comments_table	= ($db_prefix != '')
                     ? str_replace("{$this->mUnixName}_", $db_prefix, $this->mCommentsTable)
                     : $this->mCommentsTable;

      // Query to check the current version of Flyspray
      $sql	= "SELECT pref_value FROM $preferences_table WHERE pref_name = 'fs_ver'";

      // Check what version we are dealing with.
      $result = $this->mDbConnection->Execute($sql);
      if ($result)
      {
         $row = $result->FetchRow();
         if ($row['pref_value'] == '0.9.7')
         {
            // Run the upgrade script.
            if (!$this->PopulateDb($data))
            {
               return FALSE;
            }
            else
            {
				// Fix the Attachments to be within the comments
				// Get a list of the attachments
				$sql	= "
				SELECT
					*
				FROM
					$attachments_table
				WHERE
					comment_id < '1'
				AND
					date_added > '0'";

				$attachments = $this->mDbConnection->Execute($sql);
				if ($attachments)
      			{
					// Cycle through each attachment
					while($row = $attachments->FetchRow())
					{
						// Create a comment
						$sql	= "
						INSERT INTO
							flyspray_comments
							(task_id, date_added, user_id, comment_text)
						VALUES
							( ?, ?, ?, ? )";
						$data	= array($row['task_id'], $row['date_added'], $row['added_by'], $row['file_desc']);
						$this->mDbConnection->Execute($sql, $data);

						// Retrieve the comment ID
						$comment_sql	= "
						SELECT
							*
						FROM
							$comments_table
						WHERE
							comment_text = ?
						ORDER BY
							comment_id DESC";

						$comment = $this->mDbConnection->FetchRow($this->mDbConnection->Execute($comment_sql, array($row['file_desc'])));

						// Update the attachment entry to point it to the comment ID
						$update_attachments	= "
						UPDATE
							flyspray_attachments
						SET
							comment_id = ?
						WHERE
							attachment_id = ?";

						$this->mDbConnection->Execute($update_attachments, array($comment['comment_id'], $row['attachment_id']));
					}
				}
				return TRUE;
            }
         }
         else
         {
            $_SESSION['page_message'][] = 'Upgrade not Successful!';
            $_SESSION['page_message'][] = "You are trying to upgrade from the wrong {$this->mProductName} version ({$row['pref_value']}).";
            $_SESSION['page_message'][] = "You need to be having a version 0.9.7 of {$this->mProductName} installed in order to proceed with the upgrade path you have choosen.";
            return FALSE;
         }
      }
      else
      {
         $_SESSION['page_message'][] = 'Upgrade not Successful!';
         $_SESSION['page_message'][] = "Have you picked the right Upgrade path? Is your current version of {$this->mProductName}: 0.9.7 ?";
         return FALSE;
      }
   }


   function VerifyVariableTypes($type, $value)
   {
      $message = '';
      switch($type)
      {
         case 'string':
            return (!is_string($value))
            ? FALSE
            : TRUE;
         break;

         case 'number':
            return (!strval(intval($value)))
            ? FALSE
            : TRUE;
         break;

         case 'email address':
            $email_pattern = '/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i';

            if (preg_match($email_pattern, $value))
            {
               return TRUE;
            }
            return FALSE;
         break;

         case 'boolean':
            return ($value)
            ? TRUE
            : FALSE;
         break;

         case 'password':
            return (strlen($value) >= $this->mMinPasswordLength)
            ? TRUE
            : FALSE;
         break;

		 case 'folder':
		 	return (is_dir($value))
			? TRUE
			: FALSE;
		 break;

         default:
         return TRUE;
         break;
      }
   }

   /**
   * Function to output the templates
   * @param array $templates The collection of templates with their associated variables
   *
   */
   function OutputPage($templates = array())
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


$setup = new Setup;
?>
