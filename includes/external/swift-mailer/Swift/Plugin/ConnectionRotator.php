<?php

/**
 * Connection rotator plugin for Swift Mailer, a PHP Mailer class.
 * This is the second component to making Swift_Connection_Rotator handle its
 * rotation.  Without this, only one connection is used.
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

class Swift_Plugin_ConnectionRotator
{
	/**
	 * Name of the plugin (identifier)
	 * @var string plugin id
	 */
	var $pluginName = 'ConnectionRotator';
	/**
	 * Contains a reference to the main swift object.
	 * @var object swiftInstance
	 */
	var $swiftInstance;
	/**
	 * If we're waiting to do a rotate
	 * @var bool pend rotation
	 */
	var $pendRotation = false;
	
	/**
	 * Constructor.
	 */
	function Swift_Plugin_ConnectionRotator()
	{
		//
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
	 * onLoad event
	 */
	function onLoad()
	{
		//This is recursion gone crazy!
		$this->swiftInstance->connection->loadSwiftInstance($this->swiftInstance);
	}
	/**
	 * Event handler for onCommand.
	 */
	function onCommand()
	{
		if ($this->swiftInstance->commandKeyword == 'data')
		{
			$this->pendRotation = true;
		}
		elseif ($this->pendRotation)
		{
			$this->swiftInstance->connection->rotate();
			$this->pendRotation = false;
		}
	}
}

?>
