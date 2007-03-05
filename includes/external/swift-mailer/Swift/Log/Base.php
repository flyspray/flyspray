<?php

/**
 * Swift Mailer Logging Layer
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Log
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_Log");

if (!defined("SWIFT_LOG_COMMAND")) define("SWIFT_LOG_COMMAND", ">>");
if (!defined("SWIFT_LOG_RESPONSE")) define("SWIFT_LOG_RESPONSE", "<<");
if (!defined("SWIFT_LOG_ERROR")) define("SWIFT_LOG_ERROR", "!!");
if (!defined("SWIFT_LOG_NORMAL")) define("SWIFT_LOG_NORMAL", "++");

/**
 * The Base Logger class
 * @package Swift_Log
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Log_Base extends Swift_Log /*abstract*/
{
	/**
	 * A command type entry
	 */
	var $COMMAND = SWIFT_LOG_COMMAND;
	/**
	 * A response type entry
	 */
	var $RESPONSE = SWIFT_LOG_RESPONSE;
	/**
	 * An error type entry
	 */
	var $ERROR = SWIFT_LOG_ERROR;
	/**
	 * A standard entry
	 */
	var $NORMAL = SWIFT_LOG_NORMAL;
	/**
	 * Failed recipients
	 * @var array
	 */
	var $failedRecipients = array();
	/**
	 * If the logger is running or not
	 * @var boolean
	 */
	var $active = false;
	/**
	 * The maximum number of log entries
	 * @var int
	 */
	var $maxSize = 50;
	
	/**
	 * Enable logging
	 */
	function enable()
	{
		$this->active = true;
		$this->add("Enabling logging", $this->NORMAL);
	}
	/**
	 * Disable logging
	 */
	function disable()
	{
		$this->add("Disabling logging", $this->NORMAL);
		$this->active = false;
	}
	/**
	 * Check if logging is enabled
	 */
	function isEnabled()
	{
		return $this->active;
	}
	/**
	 * Add a failed recipient to the list
	 * @param string The address of the recipient
	 */
	function addFailedRecipient($address)
	{
		$this->failedRecipients[$address] = null;
	}
	/**
	 * Get the list of failed recipients
	 * @return array
	 */
	function getFailedRecipients()
	{
		return array_keys($this->failedRecipients);
	}
	/**
	 * Set the maximum size of this log (zero is no limit)
	 * @param int The maximum entries
	 */
	function setMaxSize($size)
	{
		$this->maxSize = (int) $size;
	}
	/**
	 * Get the current maximum allowed log size
	 * @return int
	 */
	function getMaxSize()
	{
		return $this->maxSize;
	}
}
