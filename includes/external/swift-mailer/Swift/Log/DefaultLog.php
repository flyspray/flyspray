<?php

/**
 * Swift Mailer Default Logger
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Log
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../Log/Base.php";

/**
 * The Default Logger class
 * @package Swift_Log
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Log_DefaultLog extends Swift_Log_Base
{
	/**
	 * Lines in the log
	 * @var array
	 */
	var $entries = array();
	
	/**
	 * Add a log entry
	 * @param string The text for this entry
	 * @param string The label for the type of entry
	 */
	function add($text, $type)
	{
		$this->entries[] = $type . " " . $text;
		if ($this->getMaxSize() > 0) $this->entries = array_slice($this->entries, (-1 * $this->getMaxSize()));
	}
	/**
	 * Dump the contents of the log to the browser
	 */
	function dump()
	{
		echo implode("\n", $this->entries);
	}
	/**
	 * Empty the log
	 */
	function clear()
	{
		$this->failedRecipients = null;
		$this->failedRecipients = array();
		$this->entries = null;
		$this->entries = array();
	}
}
