<?php
// $Id: array_combine.php,v 1.23 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_combine
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.23 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_combine($keys, $values)
{
    if (!is_array($keys)) {
        user_error('array_combine() expects parameter 1 to be array, ' .
            gettype($keys) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_array($values)) {
        user_error('array_combine() expects parameter 2 to be array, ' .
            gettype($values) . ' given', E_USER_WARNING);
        return;
    }

    $key_count = count($keys);
    $value_count = count($values);
    if ($key_count !== $value_count) {
        user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
        return false;
    }

    if ($key_count === 0 || $value_count === 0) {
        user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
        return false;
    }

    $keys    = array_values($keys);
    $values  = array_values($values);

    $combined = array();
    for ($i = 0; $i < $key_count; $i++) {
        $combined[$keys[$i]] = $values[$i];
    }

    return $combined;
}


// Define
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        return php_compat_array_combine($keys, $values);
    }
}
