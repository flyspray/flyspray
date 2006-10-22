<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2005 by Jeffery Fernandez <developer@jefferyfernandez.id.au>
// | Copyright (C) 2006  by Cristian Rodriguez R <soporte@onfocus.cl>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//

session_start();

set_time_limit(0);

ini_set('memory_limit', '32M');

/*
if ( is_readable ('../flyspray.conf.php') && (count($config = parse_ini_file('../flyspray.conf.php', true)) > 0) )
{
   die('Flyspray Already Installed. Delete the contents of flyspray.conf.php to run setup.');
}
 */

// define basic stuff first.

define('VALID_FLYSPRAY',1);
define('IN_FS', 1 );
define('APPLICATION_NAME', 'Flyspray');
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');

// get the functionality right now
$conf['general']['syntax_plugin'] = '';


require_once OBJECTS_PATH . '/fix.inc.php';
require_once OBJECTS_PATH . '/class.flyspray.php';
require_once OBJECTS_PATH . '/class.tpl.php';
require_once OBJECTS_PATH . '/version.php';


// ---------------------------------------------------------------------
// Application Web locations
// ---------------------------------------------------------------------
define('APPLICATION_SETUP_INDEX', Flyspray::absoluteURI());
define('XMLS_DEBUG', true);
define('XMLS_PREFIX_MAXLEN', 15);



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
   /**
    * @var object to store the adodb datadict object.
    */
   var $mDataDict;

   var $mXmlSchema;

   function Setup()
   {
      // Call parent constructor
      //$this->Flyspray();

      // Initialise Application values
      $mApplication				=  new Version();
      $this->mProductName	    = $mApplication->mProductName;
      $this->mVersion			= $mApplication->mVersion;
      $this->mCopyright			= $mApplication->mCopyright;
      $this->mUnixName			= $mApplication->mUnixName;
      $this->mAuthor			= $mApplication->mAuthor;
      $this->mVersion2           = $mApplication->mRelease . '.'. $mApplication->mDevLevel;
      // Look for ADOdb
      $this->mAdodbPath         = APPLICATION_PATH . '/adodb/adodb.inc.php';

      $this->mPreferencesTable	= 'flyspray_prefs';
      $this->mUsersTable		= 'flyspray_users';
      $this->mMinPasswordLength	= 8;

      // Initialise flag for proceeding to next step.
      $this->mProceed				= false;
      //according to the well known nexen.net survey, more
      //than 74 % of the installations out there runs versions
      //equal or mayor to 4.3.9 which is enough for us
      //earlier versions are really buggy anyway.
      $this->mPhpRequired			= '4.3.9';

      // If the database is supported in Flyspray, the function to check in PHP.
      $this->mSupportedDatabases	=
                           array(
                                 'MySQL' => array(true, 'mysql_connect', 'mysql'),
                                 'MySQLi' => array(true,'mysqli_connect','mysql'),
                                 'Postgres' => array(true, 'pg_connect', 'pgsql'),
                              );
      $this->mAvailableDatabases	= array();

      // Array of information to setup the appropriate tables for installation
      // or upgrade of flyspray.
      $this->mDatabaseSetup		= array (
                                    1 => array ('Install 0.9.8' => '/sql/flyspray-0.9.8', 'dependency' => '', 'function' => 'InstallPointNineEight'),
                                    2 => array ('Upgrade 0.9.7 - 0.9.8' => '/sql/upgrade_0.9.7_to_0.9.8', 'dependency' => '3', 'function' => 'UpgradePointNineSeven'),
                                    // Only for testing3 => array ('Install 0.9.7' => '/sql/flyspray-0.9.7', 'dependency' => '', 'function' => 'InstallPointNineSeven'),
                                 );

      // Process the page actions
      $this->ProcessActions();
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

      //print_r($this->mAvailableDatabases);

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
   function CheckPreStatus()
   {
      $this->mProceed = ($this->mDatabaseStatus && $this->mPhpVersionStatus);

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
   function CheckPostedData($expectedFields = array(), $pageHeading)
   {

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
    * GetSetupOptions 
    * 
    * @access public
    * @return void
    */
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

   /**
    * InstallPointNineEight 
    * 
    * @param mixed $data 
    * @access public
    * @return bool
    */
   function InstallPointNineEight($data)
   {
      return true;
   }

   /**
    * InstallPointNineSeven 
    * 
    * @param mixed $data 
    * @access public
    * @return bool
    */
   function InstallPointNineSeven($data)
   {
      return true;
   }


   /**
   * Function to check if a particular folder/file is writeable.
   * @param string $fileSystem Path to check
   * $return boolean true/false
   */
   function IsWriteable($fileSystem)
   {
      // Clear the cache
      clearstatcache();

      // Return the status of the permission
      return is_writable($fileSystem);
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
         $list .= '<li>' . $list_item .'</li>';
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
                                 'Licence Agreement', 'string', true
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
                  'db_hostname' => array('Database hostname', 'string', true),
                  'db_type' =>  array('Database type', 'string', true),
                  'db_username' => array('Database username', 'string', false),
                  'db_password' => array('Database password', 'string', false),
                  'db_name' => array('Database name', 'string', true),
                  'db_prefix' => array('Table prefix', 'string', true),
                  'db_delete' => array('Delete tables checkbox', 'string', false),
                  'db_backup' => array('Database backup checkbox', 'string', false),
                  'db_setup_options' => array('Database Setup Options', 'number', true)
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
               'db_hostname' => array('Database hostname', 'string', true),
               'db_type' =>  array('Database type', 'string', true),
               'db_username' => array('Database username', 'string', false),
               'db_password' => array('Database password', 'string', false),
               'db_name' => array('Database name', 'string', true),
               'db_prefix' => array('Table prefix', 'string', true),
               'db_setup_options' =>  array('Database type', 'number', true),
               'absolute_path' => array($this->mProductName . ' Absolute path must exist and', 'folder', true),
               'admin_username' => array('Administrator\'s username', 'string', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
               'admin_password' => array("Administrator's Password must be minimum {$this->mMinPasswordLength} characters long and", 'password', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
               'admin_email' => array('Administrator\'s email address', 'email address', ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')),
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
      $cookiesalt = substr(md5(uniqid(rand(), true)), 0, 2);

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
      $config[] = 'output_buffering = "on"				; Available options: "on" or "gzip"';
      $config[] = "passwdcrypt = \"md5\"					; Available options: \"crypt\", \"md5\", \"sha1\"";
      $config[] = "address_rewriting = \"0\"	; Boolean. 0 = off, 1 = on.";
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
         $this->mConfigFileStatus = true;
      }
      else
      {
         $this->mConfigText = $config_text;
         $this->mConfigFileStatus = false;
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
            return false;
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
      return true;
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
            return false;
            break;

            case '-24':
            // Could not connect to database with the hostname provided
            $_SESSION['page_message'][] = 'Database ' . ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
            $_SESSION['page_message'][] = 'Usually the database host name is "localhost". In some occassions, it maybe an internal ip-address or another host name to your webserver.';
            $_SESSION['page_message'][] = 'Double check with your hosting provider or System Administrator.';
            return false;
            break;

            case '-26':
            // Username passwords don't match for the hostname provided
            $_SESSION['page_message'][] = ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
            $_SESSION['page_message'][] = "Obviously you haven't set up the right permissions for the database hostname provided.";
            $_SESSION['page_message'][] = 'Double check the provided credentials or contact your System Administrator for further assistance.';
            return false;
            break;

            default:
            $_SESSION['page_message'][] = "Please verify your username/password/database details (error=$error_number)";
            return false;
            break;
         }
      }
      else
      {
           // Setting the Fetch mode of the database connection.
           $this->mDbConnection->SetFetchMode(ADODB_FETCH_BOTH);
            //creating the datadict object for further operations
           $this->mDataDict = & NewDataDictionary($this->mDbConnection);

           include_once dirname($this->mAdodbPath) . '/adodb-xmlschema03.inc.php';

            $this->mXmlSchema =  new adoSchema($this->mDbConnection);
           // Backup and delete tables if requested
           if ($this->BackupDeleteTables($data))
           {
              if ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == '')
              {
                 // Populate the database with the new tables and return the result (boolean)
                 if (!$this->PopulateDb($data))
                 {
                    return false;
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
                       return false;
                    }
                 }
                 else
                 {
                    $_SESSION['page_message'][]	= "Function {$this->mDatabaseSetup[$_POST['db_setup_options']]['function']}() not defined!";
                    return false;
                 }
              }
           }
           else
           {
              return false;
           }
      }
      return true;
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
         $bu_table = $this->mDbConnection->nameQuote . $db_bu_prefix . $table . $this->mDbConnection->nameQuote;
         
         // Query to copy the existing table into a table with the new prefix
          $sql	= "CREATE TABLE $bu_table AS SELECT * FROM $table";

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
         return false;
      }
      else
      {
         return true;
      }
   }

   /**
    * DeleteTables 
    * 
    * @param mixed $data 
    * @param mixed $table_list 
    * @access public
    * @return void
    */
   
   /* XXX : remove the $data parameter, not needed anymore */

   function DeleteTables($data, $table_list)
   {
      // Loop through the tables array
      foreach ($table_list as $table)
      {
            // Prepare the query to drop the existing tables
            $drop_table = $this->mDataDict->DropTableSQL($table);

            $this->mDataDict->ExecuteSQLArray($drop_table);

         // If any errors, record the error message in the array
         if ($error_number = $this->mDbConnection->MetaError())
         {
            $_SESSION['page_message'][] =  ucfirst($this->mDbConnection->MetaErrorMsg($error_number)) . " Error deleting: $sql";
            return false;
         }
      }
      return true;
   }


   function DeleteSequences($data)
   {
       extract($data);
       
      $db_type	= strtolower($db_type);

      if ($db_type != 'postgres') {
            return true;
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
      
      if ($result) {
          while ($row = $result->FetchRow()) {
              $sequence_list[] = $row[0];
	     }
      }

      foreach ($sequence_list as $sequence) {
          
          $this->mDbConnection->DropSequence($sequence);
	    // If any errors, record the error message in the array
	    if ($error_number = $this->mDbConnection->MetaError()) {
	        $_SESSION['page_message'][] =  ucfirst($this->mDbConnection->MetaErrorMsg($error_number));
	            return false;
	    }
      }

      return true;
   }

   function BackupDeleteTables($data)
   {
       return true;
   }
/*
   function BackupDeleteTables($data)
   {
      // Extract the data to local namespace
      extract($data);

      if ((isset($db_delete)) || (isset($db_backup)))
      {

         $table_list = array();
      }
         // Filter the tables we want to work with ..... relating it to the table prefix.
         // If the prefix given is the same as the prefix we have in the database
        // If the prefix does not match.. we can't delete or backup the intended tables
        
    
        $filtered_tables = $this->mDbConnection->MetaTables('TABLES', false, $db_prefix);
         
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
                  return false;
               }

               // Drop/backup tables only if there are no dependancies. eg. Fresh Install
               if ( ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == ''))
               {
                  // Return error if Backup was not successful.
                  if (!$this->BackupTables($data, $filtered_tables))
                  {
                     return false;
                  }
               }
               else
               {
                  $_SESSION['page_message'][] =  'Tables where not dropped because you are performing an UPGRADE.';
                  $_SESSION['page_message'][] =  'Unckeck the "delete" checkbox to proceed into the setup.';
                  return false;
               }


               // Drop tables only if there are no dependancies. eg. Fresh Install
	        if ( ($this->mDatabaseSetup[$_POST['db_setup_options']]['dependency'] == ''))
               {
                  // Return the status of deleting the tables if it was not successful.
		 if (!$this->DeleteTables($data, $filtered_tables))
		 {
		   return false;
		 }
		 if (!$this->DeleteSequences($data)) {
		   return false;
		 }
               }
               else
               {
                  $_SESSION['page_message'][] =  'Tables where not dropped because you are performing an UPGRADE.';
                  $_SESSION['page_message'][] =  'Unckeck the "delete backup" checkbox to proceed into the setup.';
                  return false;
               }
            }
            else
            {
               // Not dropping the tables unless the backup option is checked.
               $_SESSION['page_message'][] =  'Please select the "backup tables" checkbox in order to drop existing tables.';
               return false;
            }
            return true;
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
               return false;
            }

            // Return error if Backup was not successful.
            if (!$this->BackupTables($data, $filtered_tables))
            {
               return false;
            }
         }
      }
      return true;
   }

   /**
   * Function to populate the database with the sql constructs which is in an sql file
   * @param array $data The actual database configuration values
   * @return boolean
   */

   function PopulateDb($data)
   {
      // Get the sql file name and set the path

       $sql_file	= APPLICATION_PATH . '/sql/' . strtolower($this->mProductName) 
                      . '-' . $this->mVersion2 . '.'  . 'xml';
       
       // Check if the install/upgrade file exists
      if (!is_readable($sql_file)) {

          $_SESSION['page_message'][] = 'SQL file required for importing structure and data is missing.';
          return false;
      }

       // Extract the variables to local namespace
       extract($data);

       if(is_numeric($db_prefix)) {
           $_SESSION['page_message'][] = 'database prefix cannot be numeric only';
           return false;
       }
       
        // Set the prefix for database objects ( before parsing)
      $this->mXmlSchema->setPrefix($db_prefix, true);
      $this->mXmlSchema->ParseSchema($sql_file);
      
        $this->mXmlSchema->ExecuteSchema();

               if (($error_no = $this->mDbConnection->MetaError()))
               {
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

      return true;
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
               return false;
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
				return true;
            }
         }
         else
         {
            $_SESSION['page_message'][] = 'Upgrade not Successful!';
            $_SESSION['page_message'][] = "You are trying to upgrade from the wrong {$this->mProductName} version ({$row['pref_value']}).";
            $_SESSION['page_message'][] = "You need to be having a version 0.9.7 of {$this->mProductName} installed in order to proceed with the upgrade path you have choosen.";
            return false;
         }
      }
      else
      {
         $_SESSION['page_message'][] = 'Upgrade not Successful!';
         $_SESSION['page_message'][] = "Have you picked the right Upgrade path? Is your current version of {$this->mProductName}: 0.9.7 ?";
         return false;
      }
   }


   function VerifyVariableTypes($type, $value)
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
             include_once OBJECTS_PATH . '/external/Validate.php'; 
             return Validate::email($value);
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
