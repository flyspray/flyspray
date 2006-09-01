<?php

/*
 * This file is meant to add every hack that is needed to fix default PHP
 * behaviours, and to ensure that our PHP env will be able to run flyspray
 * correctly.
 *
 */

ini_set('display_errors', 1);

error_reporting(E_ALL);

// we live is register_globals Off world forever..
//This code was written By Stefan Esser from the hardened PHP project (sesser@php.net)
// it's now part of the PHP manual

function unregister_GLOBALS()
{
   if (!ini_get('register_globals')) {
       return;
   }

   // Might want to change this perhaps to a nicer error
   if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
       die('GLOBALS overwrite attempt detected');
   }

   // Variables that shouldn't be unset
   $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',    '_FILES');

   $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

   foreach ($input as $k => $v) {
       if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
           unset($GLOBALS[$k]);
       }
   }
}

unregister_GLOBALS();


/*unless we want to use this in the future, get rid of the
* the PHP >= 5.2 , input filter extension, if not, it
* will mess with user input if sysadmin or webmaster use a filter different
* than the default.
* This is based on work by Tobias Schlitt <toby@php.net> available under
* the BSD license, but has been slightly  modified for Flyspray.
*/

if (extension_loaded('filter') && input_name_to_filter(ini_get('filter.default')) !== FILTER_UNSAFE_RAW) {

    if(count($_GET)) {
        foreach ($_GET as $key => $value) {
            $_GET[$key] = input_get(INPUT_GET, $key, FILTER_UNSAFE_RAW);
        }
    }
    if(count($_POST)) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = input_get(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        }
    }
    if(count($_COOKIE)) {
        foreach ($_COOKIE as $key => $value) {
            $_COOKIE[$key] = input_get(INPUT_COOKIE, $key, FILTER_UNSAFE_RAW);
        }
    }
    if(isset($_SESSION) && is_array($_SESSION) && count($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $_SESSION[$key] = input_get(INPUT_SESSION, $key, FILTER_UNSAFE_RAW);
        }
    }

}

//then we procede with our checks and stuff

// Check PHP Version (Must Be at least 4.3)
// For 0.9.9, this should redirect to the error page
if (PHP_VERSION  < '4.3.0') {
    die('Your version of PHP is not compatible with Flyspray, '
            .'please upgrade to at least PHP version 4.3.0');
}

// This to stop PHP being retarded and using the '&' char for session id delimiters
ini_set('arg_separator.output','&amp;');

// MySQLi driver is _useless_ if zend.ze1_compatibility_mode is enabled
// in fact you should never use this setting,the damn thing does not work.

ini_set('zend.ze1_compatibility_mode',0);


//we don't want magic_quotes_runtime ..

ini_set('magic_quotes_runtime',0);

//see http://php.net/manual/en/ref.session.php#ini.session.use-only-cookies
ini_set('session.use_only_cookies',1);

//no session auto start
ini_set('session.auto_start',0);

// This is for retarded Windows servers not having REQUEST_URI

if (!isset($_SERVER['REQUEST_URI']))
{
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
    }
    else {
        $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
    }

    if ($_SERVER['QUERY_STRING']) {
        $_SERVER['REQUEST_URI'] .=  '?'.$_SERVER['QUERY_STRING'];
    }
}

/* we also don't want magic_quotes_gpc at all
 * this code was written by Ilia Alshanetsky <iilia@php.net>
 * is licensed under the BSD.
 */

function undo_magic_quotes(&$var)
{
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            if (is_array($v)) {
                array_walk($var[$k], 'undo_magic_quotes');
            } else {
                $var[$k] = stripslashes($v);
            }
        }
    } else {
        $var = stripslashes($var);
    }
}

if (ini_get('magic_quotes_gpc')) {
    if (count($_REQUEST)) {
        array_walk($_REQUEST, 'undo_magic_quotes');
    }

    if (count($_GET)) {
        array_walk($_GET,     'undo_magic_quotes');
    }

    if (count($_POST)) {
        array_walk($_POST,    'undo_magic_quotes');
    }

    if (count($_COOKIE)) {
        array_walk($_COOKIE, 'undo_magic_quotes');
    }

    if (count($_FILES) && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        array_walk($_FILES,   'undo_magic_quotes');
    }
}

/**
 * Replace array_intersect_key()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_intersect_key
 * @author      Tom Buskens <ortega@php.net>
 * @version     $Revision: 1.8 $
 * @since       PHP 5.0.2
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_intersect_key()
{
    $args = func_get_args();
    $array_count = count($args);
    if ($array_count < 2) {
        user_error('Wrong parameter count for array_intersect_key()', E_USER_WARNING);
        return;
    }

    // Check arrays
    for ($i = $array_count; $i--;) {
        if (!is_array($args[$i])) {
            user_error('array_intersect_key() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Intersect keys
    $arg_keys = array_map('array_keys', $args);
    $result_keys = call_user_func_array('array_intersect', $arg_keys);
    
    // Build return array
    $result = array();
    foreach($result_keys as $key) {
        $result[$key] = $args[0][$key];
    }
    return $result;
}

// Define
if (!function_exists('array_intersect_key')) {
    function array_intersect_key()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_intersect_key', $args);   
    }
}

?>