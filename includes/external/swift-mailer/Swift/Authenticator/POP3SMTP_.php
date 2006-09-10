<?php

/**
 * This is the POP3 Before SMTP Authentication for Swift Mailer, a PHP Mailer class.
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

/**
 * SMTP CRAM-MD5 Authenticator Class.
 * Runs the commands needed in order to use LOGIN SMTP authentication
 * @package Swift
 */
class Swift_Authenticator_POP3SMTP
{
	/**
	 * The string the SMTP server returns to identify
	 * that it supports this authentication mechanism
	 * @var string serverString
	 */
	var $serverString = '*POP-SMTP';
	/**
	 * SwiftInstance parent object
	 * @var object SwiftInstance (reference)
	 */
	var $baseObject;
	/**
	 * The port we need to connect to
	 * @var int $port
	 */
	var $port;
	/**
	 * The server we need to connect to
	 * @var string server
	 */
	var $server;
	/**
	 * The connection to POP3
	 * @var resource connect handle
	 */
	var $socket;
	
	function Swift_Authenticator_POP3SMTP($server, $port=110)
	{
		$this->port = $port;
		$this->server = $server;
	}
	/**
	 * Loads an instance of Swift to the Plugin
	 *
	 * @param	object	SwiftInstance
	 * @return	void
	 */
	function loadBaseObject(&$object)
	{
		$this->baseObject =& $object;
	}
	/**
	 * Executes the logic in the authentication mechanism
	 *
	 * @param	string	username
	 * @param	string	password
	 * @return	bool	successful
	 */
	function run($username, $password)
	{
		return $this->popB4SMTP($username, $password);
	}
	/**
	 * Connect to the POP3 server and return true on success
	 * @return bool success
	 */
	function connect()
	{
		$this->socket = @fsockopen($this->server, $this->port, $errno, $errstr, 15);
		
		if (!$this->socket) return false;
		if (!$this->isOK($this->response())) return false;
		
		return true;
	}
	/**
	 * Check for an +OK string
	 * @return bool +OK
	 */
	function isOK($string)
	{
		if (substr($string, 0, 3) == '+OK') return true;
		else return false;
	}
	/**
	 * Send a command to the server
	 */
	function command($comm)
	{
		@fwrite($this->socket, $comm);
	}
	/**
	 * Read the server response
	 * @return string response
	 */
	function response()
	{
		return fgets($this->socket);
	}
	/**
	 * Executes the logic in the authentication mechanism
	 *
	 * @param	string	username
	 * @param	string	password
	 * @return	bool	successful
	 */
	function popB4SMTP($username, $password)
	{
		//Kill any open session so we can authenticate to POP3 first
		$this->baseObject->close();
		
		if (!$this->connect()) return false;
		
		$this->command("USER $username\r\n");
		if (!$this->isOK($this->response())) return false;
		
		$this->command("PASS $password\r\n");
		if (!$this->isOK($this->response())) return false;

//START patch from Jakob Truelsen - antialize (SF.net)	
		$this->command("QUIT\r\n");
		if (!$this->isOK($this->response())) return false;
//END patch

		//Reconnect to the SMTP server
		if ($this->baseObject->connect()) return true;
		else return false;
	}
	
}

?>