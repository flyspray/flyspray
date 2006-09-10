<?php

/**
 * Error handling plugin for Swift Mailer, a PHP Mailer class.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	30th July 2006
 * @license	http://www.gnu.org/licenses/lgpl.txt Lesser GNU Public License
 *
 * @copyright Copyright &copy; 2006 Chris Corbyn - All Rights Reserved.
 * @filesource
 * 
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 2.1 of the License, or (at your option) any later version.
 *
 *   This library is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *   Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with this library; if not, write to
 *
 *   The Free Software Foundation, Inc.,
 *   51 Franklin Street,
 *   Fifth Floor,
 *   Boston,
 *   MA  02110-1301  USA
 *
 *    "Chris Corbyn" <chris@w3style.co.uk>
 *
 */

class Swift_Plugin_Errors
{
	/**
	 * Name of the plugin (identifier)
	 * @var string plugin id
	 */
	var $pluginName = 'Errors';
	/**
	 * Contains a reference to the main swift object.
	 * @var object swiftInstance
	 */
	var $swiftInstance;
	/**
	 * The norm is the echo and continue.
	 * Settting this to TRUE makes it echo the die()
	 * @var bool halt
	 */
	var $halt;
	
	/**
	 * Constructor.
	 * @param bool halt (if the script should die() on error)
	 */
	function Swift_Plugin_Errors($halt=false)
	{
		$this->halt = (bool) $halt;
	}
	/**
	 * Load in Swift
	 * @param object SwiftInstance
	 */
	function loadBaseObject(&$object)
	{
		$this->swiftInstance =& $object;
	}
	/**
	 * Event handler for onError
	 */
	function onError()
	{
		$this_error = $this->swiftInstance->lastError;
		
		$error_info = $this->getErrorStartPoint();
		
		if (!empty($error_info['class'])) $class = $error_info['class'].'::';
		else $class = '';
		
		$file_info = ' near '.$class.$error_info['function'].
			' in <strong>'.$error_info['file'].'</strong> on line <strong>'.
			$error_info['line'].'</strong><br />';
		
		$output = '<br />'.$this_error.$file_info;
		echo $output;
		if ($this->halt) exit();
	}
	/**
	 * Get the command that caused the error
	 */
	function getErrorStartPoint()
	{
		$trace = debug_backtrace();
		$start = array_pop($trace);
		return array(
			'file' => $start['file'],
			'line' => $start['line'],
			'class' => $start['class'],
			'function' => $start['function']
		);
	}
}

?>