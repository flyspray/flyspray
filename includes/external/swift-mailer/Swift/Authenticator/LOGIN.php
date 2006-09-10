<?php

/**
 * This is the LOGIN Authentication for Swift Mailer, a PHP Mailer class.
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
 * SMTP LOGIN Authenticator Class.
 * Runs the commands needed in order to use LOGIN SMTP authentication
 * @package Swift
 */
class Swift_Authenticator_LOGIN
{
	/**
	 * The string the SMTP server returns to identify
	 * that it supports this authentication mechanism
	 * @var string serverString
	 */
	var $serverString = 'LOGIN';
	/**
	 * SwiftInstance parent object
	 * @var object SwiftInstance (reference)
	 */
	var $baseObject;

	function Swift_Authenticator_LOGIN()
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
		return $this->authLOGIN($username, $password);
	}
	/**
	 * Executes the logic in the authentication mechanism
	 *
	 * @param	string	username
	 * @param	string	password
	 * @return	bool	successful
	 */
	function authLOGIN($username, $password)
	{
		$response = $this->baseObject->command("AUTH LOGIN\r\n");
		//This should be the server OK go ahead and give me a username
		preg_match('/^334\ (.*)$/', $response, $matches);
		if (!empty($matches[1]))
		{
			$decoded_response = base64_decode($matches[1]);
			if (strtolower($decoded_response) == 'username:')
			{
				$response = $this->baseObject->command(base64_encode($username));
				//This should be the server saying now give me a password
				preg_match('/^334\b\ (.*)$/', $response, $matches);
				if (!empty($matches[1]))
				{
					$decoded_response = base64_decode($matches[1]);
					if (strtolower($decoded_response) == 'password:')
					{
						//235 is a good authentication response!
						$this->baseObject->command(base64_encode($password));
						if ($this->baseObject->responseCode == 235) return true;
					}
				}
			}
		}
		//If the logic got down here then the authentication failed
		$this->baseObject->logError('Authentication failed using LOGIN', $this->baseObject->responseCode);
		$this->baseObject->fail();
		return false;
	}
}

?>
