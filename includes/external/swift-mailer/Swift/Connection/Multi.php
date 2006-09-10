<?php

/**
 * Handles connection redundancy for Swift Mailer, a PHP Mailer class.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	30th July 2006
 * @license	http://www.gnu.org/licenses/lgpl.txt Lesser GNU Public License
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
 * Multiple, redundant Connection Class.
 * Takes an array if connection objects and finds one that works
 * @package Swift
 */
class Swift_Connection_Multi
{
	/**
	 * Any errors we see
	 * @var string error
	 */
	var $error;
	/**
	 * Just a boolean value for when we're connected
	 * @var bool connected
	 */
	var $connected = false;
	/**
	 * References the readHook in the active connection
	 * @var	resource	socket (reference)
	 */
	var $readHook;
	/**
	 * References the writeHook in the active connection
	 * @var	resource	socket (reference)
	 */
	var $writeHook;
	/**
	 * The loaded connections
	 * @var array connections
	 */
	var $connections = array();
	/**
	 * The active connector
	 * @var object connection
	 */
	var $activeConnector;

	/**
	 * Constructor
	 * @param	array	connection objects
	 */
	function Swift_Connection_Multi($connections=array())
	{
		$this->connections = $connections;
	}
	/**
	 * Establishes a connection with the MTA
	 * The SwiftInstance Object calls this
	 * @return	bool	connected
	 */
	function start()
	{
		return $this->assignConnector();
	}
	/**
	 * Loops over the connections until one works
	 * @return	bool	connected
	 * @private
	 */
	function assignConnector()
	{
		$loop = false;
		//Loop over the connections
		foreach ($this->connections as $i => $obj)
		{
			//If one starts, reference class properties with the connector
			if ($this->connections[$i]->start())
			{
				$this->activeConnector =& $this->connections[$i];
				$this->connected =& $this->activeConnector->connected;
				$this->readHook =& $this->activeConnector->readHook;
				$this->writeHook =& $this->activeConnector->writeHook;
				return true;
			}
			else //Otherwise see what the problem was
			{
				if (!empty($this->connections[$i]->error))
				{
					if (!$loop) $this->error = $this->connections[$i]->error;
					else $this->error .= "; ".$this->connections[$i]->error;
				}
				
			}
			$loop = true;
		}
		//None worked...
		return false;
	}
	/**
	 * Closes the connection with the MTA
	 * Called by the SwiftInstance object
	 * @return	void
	 */
	function stop()
	{
		if ($this->connected)
		{
			$this->activeConnector->stop();
		}
	}
	/**
	 * Returns TRUE if the socket is connected
	 * @return bool connected
	 */
	function isConnected()
	{
		return $this->connected;
	}
}

?>