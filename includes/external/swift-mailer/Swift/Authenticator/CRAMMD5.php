<?php

/**
 * This is the CRAM-MD5 Authentication for Swift Mailer, a PHP Mailer class.
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
 * SMTP CRAM-MD5 Authenticator Class.
 * Runs the commands needed in order to use LOGIN SMTP authentication
 * @package Swift
 */
class Swift_Authenticator_CRAMMD5
{
	/**
	 * The string the SMTP server returns to identify
	 * that it supports this authentication mechanism
	 * @var string serverString
	 */
	var $serverString = 'CRAM-MD5';
	/**
	 * SwiftInstance parent object
	 * @var object SwiftInstance (reference)
	 */
	var $baseObject;

	function Swift_Authenticator_CRAMMD5()
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
		return $this->authCRAM_MD5($username, $password);
	}
	/**
	 * Executes the logic in the authentication mechanism
	 *
	 * @param	string	username
	 * @param	string	password
	 * @return	bool	successful
	 */
	function authCRAM_MD5($username, $password)
	{
		$response = $this->baseObject->command("AUTH CRAM-MD5\r\n");
		preg_match('/^334\ (.*)$/', $response, $matches);
		if (!empty($matches[1]))
		{
			//This response is a base64 encoded challenge "<123456.123456789@domain.tld>"
			$decoded_response = base64_decode($matches[1]);
			
			//We need to generate a digest using this challenge
			$digest = $username.' '.$this->_authGenerateCRAM_MD5_Response($password, $decoded_response);
			//We then send the username and digest as a base64 encoded string
			$auth_string = base64_encode($digest);
			$this->baseObject->command("$auth_string\r\n");
			
			if ($this->baseObject->responseCode == 235) //235 means OK
			{
				return true;
			}
		}
		$this->baseObject->logError('Authentication failed using CRAM-MD5', $this->baseObject->responseCode);
		$this->baseObject->fail();
		return false;
	}
	/**
	 * This has been lifted from a PEAR implementation at
	 * http://pear.php.net/package/Auth_SASL/
	 *
	 * @param	string	password
	 * @param	string	challenge
	 * @return	string	digest
	 */
	//This has been lifted from a PEAR implementation at
	// http://pear.php.net/package/Auth_SASL/
	function _authGenerateCRAM_MD5_Response($password, $challenge)
	{
		if (strlen($password) > 64)
			$password = pack('H32', md5($password));

		if (strlen($password) < 64)
			$password = str_pad($password, 64, chr(0));

		$k_ipad = substr($password, 0, 64) ^ str_repeat(chr(0x36), 64);
		$k_opad = substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64);

		$inner  = pack('H32', md5($k_ipad.$challenge));
		$digest = md5($k_opad.$inner);

		return $digest;
	}
}

?>
