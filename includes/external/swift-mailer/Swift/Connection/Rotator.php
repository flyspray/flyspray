<?php

/**
 * Handles connection cycling & load balancing for Swift Mailer, a PHP Mailer class.
 * This requires the plugin "Swift_Connection_Rotator_Plugin" to actually rotate.
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
 * Allows rotating of multiple connections on each send
 * Takes an array if connection objects, find the ones that work, then offers a rotate() option
 * @package Swift
 */
class Swift_Connection_Rotator
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
	 * Contains references to the connections which work
	 * @var array connections
	 */
	var $workingConnections = array();
	/**
	 * The active connector
	 * @var object connection
	 */
	var $activeConnector;
	/**
	 * The key of the active connection
	 */
	var $connectionIdex = 0;
	/**
	 * Last index in connections tried
	 * @var int index
	 */
	var $lastIndex = -1;
	/**
	 * An instance of Swift
	 * @var object Swift
	 */
	var $swiftInstance;
	/**
	 * If we've tried to use all of the connections
	 */
	var $allTried = false;

	/**
	 * Constructor
	 * @param	array	connection objects
	 */
	function Swift_Connection_Rotator($connections=array())
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
		return $this->startConnector();
	}
	/**
	 * Load in Swift
	 * @param object Swift
	 */
	function loadSwiftInstance(&$object)
	{
		$this->swiftInstance =& $object;
	}
	/**
	 * Loops over the connections and get one that works
	 * @return	bool	connected
	 * @private
	 */
	function startConnector()
	{
		$loop = false;
		//Loop over the connections
		for ($i = $this->lastIndex+1; $i < count($this->connections); $i++)
		{
			//If one starts, reference class properties with the connector
			if ($this->connections[$i]->start())
			{
				$this->lastIndex = $i;
				$this->activeConnector =& $this->connections[$i];
				$this->connected =& $this->activeConnector->connected;
				$this->readHook =& $this->activeConnector->readHook;
				$this->writeHook =& $this->activeConnector->writeHook;
				$this->workingConnections[] =& $this->connections[$i];
				$this->connectionIndex = count($this->workingConnections)-1;
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
		$this->allTried = true;
		return false;
	}
	/**
	 * Move onto the next connector
	 */
	function rotate()
	{
		if (!$this->allTried)
		{
			//This re-assigns the connection anyway
			if ($this->startConnector())
			{
				//We'll need to do a handshake because it's the first connect
				$this->swiftInstance->handshake();
			}
		}
		else
		{
			//Can we go forward?
			if (isset($this->workingConnections[$this->connectionIndex+1]))
			{
				$this->connectionIndex++;
			}
			else //Ok, then we go back to the start instead
			{
				$this->connectionIndex = 0;
			}
			$this->activeConnector =& $this->workingConnections[$this->connectionIndex];
			$this->connected =& $this->activeConnector->connected;
			$this->readHook =& $this->activeConnector->readHook;
			$this->writeHook =& $this->activeConnector->writeHook;
		}
	}
	/**
	 * Closes the connections with the MTA
	 * Called by the SwiftInstance object
	 * @return	void
	 */
	function stop()
	{
		//Close all connections that opened
		foreach ($this->workingConnections as $i => $obj)
		{
			if ($this->workingConnections[$i]->isConnected())
			{
				$this->workingConnections[$i]->stop();
			}
		}
		$this->workingConnections = array();
		$this->connectionIndex = 0;
		$this->lastTried = -1;
		$this->connected = false;
		$this->activeConnection = null;
		$this->readHook = null;
		$this->writeHook = null;
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