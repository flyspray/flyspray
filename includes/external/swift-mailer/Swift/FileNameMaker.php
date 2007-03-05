<?php

/**
 * Swift Mailer File name making component (to avoid clashes)
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

/**
 * File name maker (makes filenames in sequence)
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_FileNameMaker
{
	/**
	 * Just a number to increment
	 * @var int
	 */
	var $id = 1;
	
	/**
	 * Singleton Factory
	 * @return Swift_FileNameMaker
	 */
	function &instance()
	{
		static $instance = null;
		if (!$instance) $instance = array(new Swift_FileNameMaker());
		
		return $instance[0];
	}
	/**
	 * Get a unique filename (just a sequence)
	 * @param string the prefix for the filename
	 * @return string
	 */
	function Generate($prefix="file")
	{
		return $prefix . ($this->id++);
	}
}