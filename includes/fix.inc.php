<?php

/*
 * This file is meant to add every hack that is needed to fix default PHP
 * behaviours, and to ensure that our PHP env will be able to run flyspray
 * correctly.
 *
 */
ini_set('display_errors', 1);

// html errors will mess the layout
ini_set('html_errors', 0);

//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_STRICT);

// our default charset

ini_set('default_charset','utf-8');

// This to stop PHP being retarded and using the '&' char for session id delimiters
ini_set('arg_separator.output','&amp;');

// no transparent session id improperly configured servers
if (strpos($_SERVER['REQUEST_URI'], '/setup/') === false) // Skip installer, as it starts the session before calling fix.inc.php causing a warning as this can't be used when a session is already active
	ini_set('session.use_trans_sid', 0);

//see http://php.net/manual/en/ref.session.php#ini.session.use-only-cookies
ini_set('session.use_only_cookies',1);

//no session auto start
ini_set('session.auto_start',0);

/*this stops most cookie attacks via XSS at the interpreter level
* see http://msdn.microsoft.com/workshop/author/dhtml/httponly_cookies.asp
* supported by IE 6 SP1, Safari, Konqueror, Opera, silently ignored by others
* ( sadly, including firefox) available since PHP 5.2.0
 */

ini_set('session.cookie_httponly',1);

// use stronger entropy in sessions whenever possible
ini_set('session.entropy_file', '/dev/urandom');
ini_set('session.entropy_length', 16);
// use sha-1 for sessions
ini_set('session.hash_function',1);

ini_set('auto_detect_line_endings', 0);

ini_set('include_path', join( PATH_SEPARATOR, array(
  dirname(__FILE__) . '/external' ,
  ini_get('include_path'))));


if(count($_GET)) {
    foreach ($_GET as $key => $value) {
	if(is_array($value))
        	$_GET[$key] = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
	else
        	$_GET[$key] = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);
    }
}
if(count($_POST)) {
    foreach ($_POST as $key => $value) {
	if(is_array($value))
        	$_POST[$key] = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
	else
		$_POST[$key] = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
    }
}
if(count($_COOKIE)) {
    foreach ($_COOKIE as $key => $value) {
	if(is_array($value))
        	$_COOKIE[$key] = filter_input(INPUT_COOKIE, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
	else
		$_COOKIE[$key] = filter_input(INPUT_COOKIE, $key, FILTER_UNSAFE_RAW);
    }
}
if(isset($_SESSION) && is_array($_SESSION) && count($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
	if(is_array($value))
        	$_SESSION[$key] = filter_input(INPUT_SESSION, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
	else
		$_SESSION[$key] = filter_input(INPUT_SESSION, $key, FILTER_UNSAFE_RAW);
    }
}


// This is for retarded Windows servers not having REQUEST_URI

if (!isset($_SERVER['REQUEST_URI']))
{
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
    }
    else {
        // this is tained now.
        $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
    }

    if (isset($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .=  '?'.$_SERVER['QUERY_STRING'];
    }
}

if (!isset($_SERVER['QUERY_STRING']))
{
    $_SERVER['QUERY_STRING'] = '';
}


/**
 * Replace glob() since this function is apparently
 * disabled for no apparent reason ("security") on some systems
 *
 * @see glob()
 * @require     PHP 4.3.0 (fnmatch)
 * @todo is this still required?
 */
function glob_compat($pattern, $flags = 0) {

    $split = explode('/', $pattern);
    $match = array_pop($split);
    $path = implode('/', $split);
    if (($dir = opendir($path)) !== false) {
        $glob = array();
        while (($file = readdir($dir)) !== false) {
            if (fnmatch($match, $file)) {
                if (is_dir("$path/$file") || !($flags & GLOB_ONLYDIR)) {
                    if ($flags & GLOB_MARK) $file .= '/';
                    $glob[] = $file;
                }
            }
        }
        closedir($dir);
        if (!($flags & GLOB_NOSORT)) sort($glob);
        return $glob;
    }
    return false;
}

// now for all those borked PHP installations...
// TODO still required. Enabled by default since 4.2
if (!function_exists('ctype_alnum')) {
	function ctype_alnum($text) {
		return is_string($text) && preg_match('/^[a-z0-9]+$/iD', $text);
	}
}
if (!function_exists('ctype_digit')) {
	function ctype_digit($text) {
        return is_string($text) && preg_match('/^[0-9]+$/iD', $text);
	}
}

if(!isset($_SERVER['SERVER_NAME']) && php_sapi_name() === 'cli') {
    $_SERVER['SERVER_NAME'] = php_uname('n');
}

//for reasons outside flsypray, the PHP core may throw Exceptions in PHP5
// for a good example see this article
// http://ilia.ws/archives/107-Another-unserialize-abuse.html

function flyspray_exception_handler($exception) {

    die("Completely unexpected exception: " .
        htmlspecialchars($exception->getMessage(),ENT_QUOTES, 'utf-8')  . "<br/>" .
      "This should <strong> never </strong> happend, please inform Flyspray Developers");

}

set_exception_handler('flyspray_exception_handler');


// We don't need session IDs in URLs
output_reset_rewrite_vars();

