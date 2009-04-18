<?php
// $Id: http_build_query.php 1265 2007-06-01 01:36:05Z judas_iscariote $


/**
 * Replace function http_build_query()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.http-build-query
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.22 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_http_build_query($formdata, $numeric_prefix = null, $arg_separator = null)
{
    // If $formdata is an object, convert it to an array
    if (is_object($formdata)) {
        $formdata = get_object_vars($formdata);
    }

    // Check we have an array to work with
    if (!is_array($formdata)) {
        user_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.',
            E_USER_WARNING);
        return false;
    }

    // If the array is empty, return null
    if (empty($formdata)) {
        return;
    }

    // Argument seperator
    $separator = $arg_separator ? $arg_separator : ini_get('arg_separator.output');
    if (strlen($separator) == 0) {
        $separator = '&';
    }

    // Start building the query
    $tmp = array ();
    foreach ($formdata as $key => $val) {
        if (is_null($val)) {
            continue;
        }

        if (is_integer($key) && $numeric_prefix != null) {
            $key = $numeric_prefix . $key;
        }

        if (is_scalar($val)) {
            array_push($tmp, urlencode($key) . '=' . urlencode($val));
            continue;
        }

        // If the value is an array, recursively parse it
        if (is_array($val) || is_object($val)) {
            array_push($tmp, php_compat_http_build_query_helper($val, urlencode($key)));
            continue;
        }

        // The value is a resource
        return null;
    }

    return implode($separator, $tmp);
}


// Helper function
function php_compat_http_build_query_helper($array, $name)
{
    $tmp = array ();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            array_push($tmp, php_compat_http_build_query_helper($value, sprintf('%s[%s]', $name, $key)));
        } elseif (is_scalar($value)) {
            array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
        } elseif (is_object($value)) {
            array_push($tmp, php_compat_http_build_query_helper(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
        }
    }

    // Argument seperator
    $separator = ini_get('arg_separator.output');
    if (strlen($separator) == 0) {
        $separator = '&';
    }

    return implode($separator, $tmp);
}


// Define
if (!function_exists('http_build_query')) {
    function http_build_query($formdata, $numeric_prefix = null)
    {
        return php_compat_http_build_query($formdata, $numeric_prefix);
    }
}
