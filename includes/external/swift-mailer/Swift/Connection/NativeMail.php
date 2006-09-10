<?php

/**
 * This is the mail() handler for Swift Mailer, a PHP Mailer class.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	24th August 2006
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

//Requires the Swift SMTP Stream library
require_once dirname(__FILE__).'/../Stream.php';
require_once dirname(__FILE__).'/../Stream/Processor.php';
require_once dirname(__FILE__).'/../Stream/MailProxy.php';

class Swift_Connection_NativeMail
{
	/**
	 * Just a boolean value for when we're connected
	 * @var bool connected
	 */
	var $connected = false;
	/**
	 * SMTP Connection socket
	 * @var	resource	socket
	 */
	var $socket;
	/**
	 * SMTP Read part of I/O for Swift
	 * @var	resource	socket (reference)
	 */
	var $readHook;
	/**
	 * SMTP Write part of I/O for Swift
	 * @var	resource	socket (reference)
	 */
	var $writeHook;
	/**
	 * The fake plugin can also have a fake username and password
	 * @var string username
	 */

	/**
	 * Constructor
	 * @param array faked extensions
	 */
	function Swift_Connection_NativeMail()
	{
		SmtpMsgStub::setExtensions(array());
	}
	/**
	 * Establishes a connection with the MTA
	 * The SwiftInstance Object calls this
	 *
	 * @return	bool	connected
	 */
	function start()
	{
		return $this->connect();
	}
	/**
	 * Establishes a connection with the MTA
	 *
	 * @return	bool	connected
	 * @private
	 */
	function connect()
	{
		$this->socket = fopen('swift://esmtp', 'w+');
		$processor =& Swift_Stream_Processor::getInstance();
		
		$processor->addObserver(new Swift_Stream_MailProxy($processor));
		
		$this->readHook =& $this->socket;
		$this->writeHook =& $this->socket;

		if (!$this->socket) return $this->connected = false;
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
		$this->disconnect();
	}
	/**
	 * Closes the connection with the MTA
	 * @return	void
	 */
	function disconnect()
	{
		if ($this->connected && $this->socket)
		{
			fclose($this->socket);
			$this->readHook = false;
			$this->writeHook = false;
			$this->socket = false;
			
			$this->connected = false;
		}
	}
	/**
	 * Returns TRUE if the socket is connected
	 * @return bool connected
	 */
	function isConnected()
	{
		return $this->connected;
	}
}

?>