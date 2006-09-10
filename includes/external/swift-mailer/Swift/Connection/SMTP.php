<?php

/**
 * This is the SMTP handler for Swift Mailer, a PHP Mailer class.
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

if (!defined('SWIFT_OPEN')) define('SWIFT_OPEN', 0);
if (!defined('SWIFT_SSL')) define('SWIFT_SSL', 1);
if (!defined('SWIFT_TLS')) define('SWIFT_TLS', 2);
if (!defined('SWIFT_DEFAULT_PORT')) define('SWIFT_DEFAULT_PORT', 25);
if (!defined('SWIFT_SECURE_PORT')) define('SWIFT_SECURE_PORT', 465);
if (!defined('SWIFT_AUTO_DETECT')) define('SWIFT_AUTO_DETECT', -2);

/**
 * SMTP Connection Class.
 * Connects to a remote MTA and stores the connections internally
 * @package Swift
 */
class Swift_Connection_SMTP
{
	/**
	 * Any errors we see
	 * @var string error
	 */
	var $error;
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
	 * Number of seconds to try to connect for (non-settable)
	 * @var	int		seconds timeout
	 */
	var $connectTimeout = 30;
	/**
	 * SMTP server
	 * @var	resource	socket
	 */
	var $server;
	/**
	 * SMTP Port (default 25)
	 * @var	int		port number
	 */
	var $port = SWIFT_DEFAULT_PORT;
	/**
	 * Use SSL Encryption
	 * @var	bool	SSL
	 */
	var $ssl;

	/**
	 * Constructor
	 * @param	string	SMTP server
	 * @param	int		SMTP Port, optional
	 * @param	bool	SSL
	 */
	function Swift_Connection_SMTP($server, $port=false, $transport=SWIFT_OPEN)
	{
		if ($server == SWIFT_AUTO_DETECT) $server = @ini_get('SMTP');
		if ($port == SWIFT_AUTO_DETECT) $port = @ini_get('smtp_port');
		
		$this->server = $server;
		if ($port) $this->port = $port;
		$this->ssl = $transport;
		if ($transport && !$port)
		{
			$this->port = SWIFT_SECURE_PORT;
		}
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
		$server = $this->server;
		
		switch ($this->ssl)
		{
			case SWIFT_SSL: $protocol = 'ssl://';
			break;
			case SWIFT_TLS: $protocol = 'tls://';
			break;
			case SWIFT_OPEN:
			default: $protocol = '';
		}
		
		$server = $protocol.$server;
		
		$this->socket = @fsockopen($server, $this->port, $errno, $errstr, $this->connectTimeout);
		
		$this->readHook =& $this->socket;
		$this->writeHook =& $this->socket;

		if (!$this->socket)
		{
			$this->error = $errstr;
			return $this->connected = false;
		}
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
	/**
	 * Change the default timeout from 30 seconds
	 * @param int timeout secs
	 * @return void
	 */
	function setConnectTimeout($seconds)
	{
		$this->connectTimeout = (int) $seconds;
	}
}

?>