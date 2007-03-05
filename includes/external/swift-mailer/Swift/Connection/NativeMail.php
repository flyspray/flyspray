<?php

/**
 * Swift Mailer mail() connection component
 * Please read the LICENSE file
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Connection
 * @license GNU Lesser General Public License
 */
 
require_once dirname(__FILE__). "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_ConnectionBase");

/**
 * Swift mail() Connection
 * NOTE: This class is nothing more than a stub.  The MailSend plugin does the actual sending.
 * @package Swift_Connection
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Connection_NativeMail extends Swift_ConnectionBase
{
	/**
	 * The response the stub will be giving next
	 * @var string Response
	 */
	var $response = "220 Stubbed";
	
	/**
	 * Sets the MailSend plugin in Swift once Swift has connected
	 * @param Swift The current instance of Swift
	 */
	function postConnect(&$instance)
	{
		Swift_ClassLoader::load("Swift_Plugin_MailSend");
		$instance->attachPlugin(new Swift_Plugin_MailSend(), "_MAIL_SEND");
	}
	/**
	 * Read a full response from the buffer (this is spoofed if running in -t mode)
	 * @return string
	 * @throws Swift_Connection_Exception Upon failure to read
	 */
	function read()
	{
		return $this->response;
	}
	/**
	 * Set the response this stub will return
	 * @param string The response to send
	 */
	function setResponse($int)
	{
		$this->response = $int . " Stubbed";
	}
	/**
	 * Write a command to the process (leave off trailing CRLF)
	 * @param string The command to send
	 * @throws Swift_Connection_Exception Upon failure to write
	 */
	function write($command, $end="\r\n")
	{
		$command = strtoupper($command);
		if (strpos($command, " ")) $command = substr($command, 0, strpos($command, " "));
		switch ($command)
		{
			case "DATA":
				$this->setResponse(354);
				break;
			case "EHLO": case "MAIL": case "RCPT": case "QUIT": case "RSET": default:
				$this->setResponse(250);
				break;
		}
	}
	/**
	 * Try to start the connection
	 * @throws Swift_Connection_Exception Upon failure to start
	 */
	function start()
	{
		$this->response = "220 Stubbed";
		return true;
	}
	/**
	 * Try to close the connection
	 * @throws Swift_Connection_Exception Upon failure to close
	 */
	function stop()
	{
		$this->response = "220 Stubbed";
	}
	/**
	 * Check if the process is still alive
	 * @return boolean
	 */
	function isAlive()
	{
		return function_exists("mail");
	}
}
