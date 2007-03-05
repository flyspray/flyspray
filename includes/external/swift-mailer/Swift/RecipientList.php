<?php

/**
 * Swift Mailer Recipient List Container
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/ClassLoader.php";
Swift_ClassLoader::load("Swift_Address");

/**
 * Swift's Recipient List container.  Contains To, Cc, Bcc
 * @package Swift
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_RecipientList extends Swift_AddressContainer
{
	/**
	 * The recipients in the To: header
	 * @var array
	 */
	var $to = array();
	/**
	 * The recipients in the Cc: header
	 * @var array
	 */
	var $cc = array();
	/**
	 * The recipients in the Bcc: header
	 * @var array
	 */
	var $bcc = array();
	
	/**
	 * Add a To: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	function addTo($address, $name=null)
	{
		if (is_a($address, "Swift_Address"))
		{
			$this->to[$address->getAddress()] =& $address;
		}
		else
		{
			$address = (string) $address;
			$this->to[$address] =& new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the To: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	function &getTo()
	{
		return $this->to;
	}
	/**
	 * Remove a To: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	function removeTo($address)
	{
		if (is_a($address, "Swift_Address"))
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->to)) unset($this->to[$key]);
	}
	/**
	 * Empty all To: addresses
	 */
	function flushTo()
	{
		$this->to = null;
		$this->to = array();
	}
	/**
	 * Add a Cc: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	function addCc($address, $name=null)
	{
		if (is_a($address, "Swift_Address"))
		{
			$this->cc[$address->getAddress()] =& $address;
		}
		else
		{
			$address = (string) $address;
			$this->cc[$address] =& new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the Cc: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	function &getCc()
	{
		return $this->cc;
	}
	/**
	 * Remove a Cc: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	function removeCc($address)
	{
		if (is_a($address, "Swift_Address"))
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->cc)) unset($this->cc[$key]);
	}
	/**
	 * Empty all Cc: addresses
	 */
	function flushCc()
	{
		$this->cc = null;
		$this->cc = array();
	}
	/**
	 * Add a Bcc: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	function addBcc($address, $name=null)
	{
		if (is_a($address, "Swift_Address"))
		{
			$this->bcc[$address->getAddress()] =& $address;
		}
		else
		{
			$address = (string) $address;
			$this->bcc[$address] =& new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the Bcc: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	function &getBcc()
	{
		return $this->bcc;
	}
	/**
	 * Remove a Bcc: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	function removeBcc($address)
	{
		if (is_a($address, "Swift_Address"))
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->bcc)) unset($this->bcc[$key]);
	}
	/**
	 * Empty all Bcc: addresses
	 */
	function flushBcc()
	{
		$this->bcc = null;
		$this->bcc = array();
	}
	/**
	 * Empty the entire list
	 */
	function flush()
	{
		$this->flushTo();
		$this->flushCc();
		$this->flushBcc();
	}
}
