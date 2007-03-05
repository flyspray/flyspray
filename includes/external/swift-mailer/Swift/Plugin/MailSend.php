<?php

/**
 * Swift Mailer mail() sending plugin
 * Please read the LICENSE file
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Connection
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_Events_Listener");

/**
 * Swift mail() send plugin
 * Sends the message using mail() when a SendEvent is fired.  Using the NativeMail connection provides stub responses to allow this to happen cleanly.
 * @package Swift_Connection
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Plugin_MailSend extends Swift_Events_Listener
{
	/**
	 * The operating system of the server
	 * @var string
	 */
	var $OS = null;
	/**
	 * The return path in use here
	 * @var string
	 */
	var $returnPath = null;
	
	/**
	 * Constructor
	 */
	function Swift_Plugin_MailSend()
	{
		$this->setOS(PHP_OS);
	}
	/**
	 * Set the operating system string (changes behaviour with LE)
	 * @param string The operating system
	 */
	function setOS($os)
	{
		$this->OS = $os;
	}
	/**
	 * Get the operating system string
	 * @return string
	 */
	function getOS()
	{
		return $this->OS;
	}
	/**
	 * Check if this is windows or not
	 * @return boolean
	 */
	function isWindows()
	{
		return (substr($this->getOS(), 0, 3) == "WIN");
	}
	/**
	 * Swift's SendEvent listener.
	 * Invoked when Swift sends a message
	 * @param Swift_Events_SendEvent The event information
	 * @throws Swift_Connection_Exception If mail() returns false
	 */
	function sendPerformed(&$e)
	{
		$message =& $e->getMessage();
		$recipients =& $e->getRecipients();
		
		$to = array();
		foreach ($recipients->getTo() as $addr)
		{
			if ($this->isWindows()) $to[] = substr($addr->build(true), 1, -1);
			else $to[] = $addr->build();
		}
		$to = implode(", ", $to);
		
		$bcc_orig = $message->headers->has("Bcc") ? $message->headers->get("Bcc") : null;
		
		$bcc = array();
		foreach ($recipients->getBcc() as $addr) $bcc[] = $addr->build();
		if (!empty($bcc)) $message->headers->set("Bcc", $bcc);
		$bcc = null;
		
		$le = $message->getLE();
		if (!$this->isWindows() && $le != "\n") $message->setLE("\n");
		$body_data =& $message->buildData();
		$message_body = $body_data->readFull();
		$message_headers = $message->headers; //We're going to screw around with the headers so we'll copy rather than reference
		$message->headers->set("Bcc", $bcc_orig);
		
		$subject = $message_headers->has("Subject") ? $message_headers->getEncoded("Subject") : "";
		
		$message_headers->set("To", null);
		$message_headers->set("Subject", null);
		
		$sender =& $e->getSender();
		$this->returnPath = $sender->build();
		if ($message_headers->has("Return-Path")) $this->returnPath = $message_headers->get("Return-Path");
		if (preg_match("~<([^>]+)>[^>]*\$~", $this->returnPath, $matches)) $this->returnPath = $matches[1];
		
		$this->doMail($to, $subject, $message_body, $message_headers, "-oi -f " . $this->returnPath);
	}
	
	function doMail($to, $subject, $message, $headers, $params)
	{
		$original_from = @ini_get("sendmail_from");
		@ini_set("sendmail_from", $this->returnPath);
		
		$headers = $headers->build();
		if (!@mail($to, $subject, $message, $headers, $params))
		{
			@ini_set("sendmail_from", $original_from);
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"Sending failed using mail() as PHP's default mail() function returned boolean FALSE."));
			return;
		}
		@ini_set("sendmail_from", $original_from);
	}
}
