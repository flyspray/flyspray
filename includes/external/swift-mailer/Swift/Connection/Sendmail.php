<?php

/**
 * This is the Sendmail handler for Swift Mailer, a PHP Mailer class.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	30th July 2006
 * @license http://www.gnu.org/licenses/lgpl.txt Lesser GNU Public License
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

if (!defined('SWIFT_AUTO_DETECT')) define('SWIFT_AUTO_DETECT', -2);

/**
 * Sendmail Connection Class.
 * Connects to a local binary MTA and stores the connections internally
 * @package Swift
 */
class Swift_Connection_Sendmail
{
	/**
	 * Sendmail Command (full path and arguments)
	 * This can be a different MTA that behaves like sendmail
	 * Exim works here.
	 * @var	string	command
	 */
	var $command = "/usr/sbin/sendmail -bs";
	/**
	 * Sendmail Process handle
	 * @var	resource	handle
	 */
	var $handle;
	/**
	 * Process Pipes from proc_open()
	 * @var	array	pipes
	 */
	var $pipes;
	/**
	 * MTA Read part of I/O for Swift
	 * @var	resource	socket (reference)
	 */
	var $readHook;
	/**
	 * MTA Write part of I/O for Swift
	 * @var	resource	socket (reference)
	 */
	var $writeHook;
	/**
	 * If the server is connected to the MTA.
	 * @var bool connected
	 */
	var $connected = false;
	
	/**
	 * Constructor
	 * @param	string	Sendmail command, optional
	 */
	function Swift_Connection_Sendmail($command=false)
	{
		if ($command == SWIFT_AUTO_DETECT) $command = @trim(`which sendmail`).' -bs';
		if ($command) $this->command = $command;
	}
	/**
	 * Establishes a connection with the MTA
	 * The SwiftInstance Object calls this
	 *
	 * @return	bool	connected
	 */
	function start()
	{
		return $this->initializeProcess();
	}
	/**
	 * Establishes a connection with the MTA
	 *
	 * @return	bool	connected
	 */
	function initializeProcess()
	{
		$pipes_spec = array(
			array("pipe", "r"),
			array("pipe", "w"),
			array("pipe", "w")
		);
		$this->handle = @proc_open($this->command, $pipes_spec, $this->pipes);
		
		$this->writeHook =& $this->pipes[0];
		$this->readHook =& $this->pipes[1];
		
		if (!$this->handle) return $this->connected = false;
		else return $this->connected = true;
	}
	/**
	 * Closes the connection with the MTA
	 * Called by the SwiftInstance object
	 *
	 * @return	void
	 */
	function stop()
	{
		$this->closeProcess();
	}
	/**
	 * Closes the connection with the MTA
	 *
	 * @return	void
	 */
	function closeProcess()
	{
		foreach ($this->pipes as $pipe) fclose($pipe);
		if ($this->handle && $this->connected)
		{
			proc_close($this->handle);
			$this->writeHook = false;
			$this->readHook = false;
			$this->connected = false;
		}
	}
	/**
	 * Returns TRUE if we're connected to the MTA
	 */
	function isConnected()
	{
		return $this->connected;
	}
}

?>