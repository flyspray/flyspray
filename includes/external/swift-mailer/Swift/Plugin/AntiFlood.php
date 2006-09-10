<?php

/**
 * Anti-Flood plugin for Swift Mailer, a PHP Mailer class.
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

class Swift_Plugin_AntiFlood
{
	/**
	 * Name of the plugin (identifier)
	 * @var string plugin id
	 */
	var $pluginName = 'Anti_Flood';
	/**
	 * The maximum number of messages to send
	 * over a single connection
	 * @var int max messages
	 */
	var $maxMessages;
	/**
	 * The time to wait for before reconnecting
	 * @var int sleep seconds
	 */
	var $sleep;
	/**
	 * Current messages sent since last reconnect
	 * or plugin loading.
	 * @var int current messages
	 */
	var $currMessages = 0;
	/**
	 * Contains a reference to the main swift object.
	 * @var object swiftInstance
	 */
	var $swiftInstance;
	
	/**
	 * Constructor.
	 * @param int max messages, optional
	 * @return void
	 */
	function Swift_Plugin_AntiFlood($max=10, $sleep=0)
	{
		$this->maxMessages = (int) $max;
		$this->sleep = (int) $sleep;
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
	 * Event handler for onSend.
	 */
	function onSend()
	{
		$this->currMessages++;
		if ($this->currMessages >= $this->maxMessages)
		{
			$this->reconnect();
			$this->currMessages = 0;
		}
	}
	/**
	 * Reconnect to the server
	 */
	function reconnect()
	{
		$this->swiftInstance->close();
		
		//Wait for N seconds if needed to give the server a rest
		if ($this->sleep) sleep($this->sleep);
		
		$this->swiftInstance->connect();
		//Re-authenticate if needs be
		if (!empty($this->swiftInstance->username))
		{
			$this->swiftInstance->authenticate(
				$this->swiftInstance->username,
				$this->swiftInstance->password
			);
		}
	}
}

?>
