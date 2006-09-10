<?php

/**
 * This is the PLAIN Authentication for Swift Mailer, a PHP Mailer class.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	4th August 2006
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
 * SMTP PLAIN Authenticator Class.
 * Runs the commands needed in order to use PLAIN SMTP authentication
 * @package Swift
 */
class Swift_Authenticator_PLAIN
{
	/**
	 * The string the SMTP server returns to identify
	 * that it supports this authentication mechanism
	 * @var string serverString
	 */
	var $serverString = 'PLAIN';
	/**
	 * SwiftInstance parent object
	 * @var object SwiftInstance (reference)
	 */
	var $baseObject;

	function Swift_Authenticator_PLAIN()
	{
		//
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
		return $this->authPLAIN($username, $password);
	}
	/**
	 * Executes the logic in the authentication mechanism
	 *
	 * @param	string	username
	 * @param	string	password
	 * @return	bool	successful
	 * @private
	 */
	function authPLAIN($username, $password)
	{
		//The authorization string uses ascii null as a separator (See RFC 2554)
		$auth_string = base64_encode("$username\0$username\0$password");
		$this->baseObject->command("AUTH PLAIN $auth_string\r\n");
		//This should be the server saying OK
		if ($this->baseObject->responseCode == 235)
		{
			return true;
		}

		$this->baseObject->logError('Authentication failed using PLAIN', $this->baseObject->responseCode);
		$this->baseObject->fail();
		return false;
	}
}

?>
