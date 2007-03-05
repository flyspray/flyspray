<?php

/**
 * Swift Mailer Send Event
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Events
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_Events");

/**
 * Generated every time a message is sent with Swift
 * @package Swift_Events
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Events_SendEvent extends Swift_Events
{
	/**
	 * A reference to the message being sent
	 * @var Swift_Message
	 */
	var $message = null;
	/**
	 * A reference to the sender address object
	 * @var Swift_Address
	 */
	var $sender = null;
	/**
	 * A reference to the recipients being sent to
	 * @var Swift_RecipientList
	 */
	var $recipients = null;
	/**
	 * The number of recipients sent to so
	 * @var int
	 */
	var $sent = null;
	
	/**
	 * Constructor
	 * @param Swift_Message The message being sent
	 * @param Swift_RecipientList The recipients
	 * @param Swift_Address The sender address
	 * @param int The number of addresses sent to
	 */
	function Swift_Events_SendEvent(&$message, &$list, &$from, $sent=0)
	{
		$this->message =& $message;
		$this->recipients =& $list;
		$this->sender =& $from;
		$this->sent = $sent;
	}
	/**
	 * Get the message being sent
	 * @return Swift_Message
	 */
	function &getMessage()
	{
		return $this->message;
	}
	/**
	 * Get the list of recipients
	 * @return Swift_RecipientList
	 */
	function &getRecipients()
	{
		return $this->recipients;
	}
	/**
	 * Get the sender's address
	 * @return Swift_Address
	 */
	function &getSender()
	{
		return $this->sender;
	}
	/**
	 * Set the number of recipients to how many were sent
	 * @param int
	 */
	function setNumSent($sent)
	{
		$this->sent = (int) $sent;
	}
	/**
	 * Get the total number of addresses to which the email sent successfully
	 * @return int
	 */
	function getNumSent()
	{
		return $this->sent;
	}
}
