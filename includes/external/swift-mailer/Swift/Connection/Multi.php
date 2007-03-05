<?php

/**
 * Swift Mailer Multiple Redundant Connection component.
 * Please read the LICENSE file
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Connection
 * @license GNU Lesser General Public License
 */
 
require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_ConnectionBase");

/**
 * Swift Multi Connection
 * Tries to connect to a number of connections until one works successfully
 * @package Swift_Connection
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Connection_Multi extends Swift_ConnectionBase
{
	/**
	 * The list of available connections
	 * @var array
	 */
	var $connections = array();
	/**
	 * The id of the active connection
	 * @var string
	 */
	var $active = null;
	
	/**
	 * Constructor
	 */
	function Swift_Connection_Multi($connections=array())
	{
		foreach ($connections as $id => $conn)
		{
			$this->addConnection($connections[$id], $id);
		}
	}
	/**
	 * Add a connection to the list of options
	 * @param Swift_Connection An instance of the connection
	 * @param string An ID to assign to the connection
	 */
	function addConnection(&$connection, $id=null)
	{
		if (!is_a($connection, "Swift_Connection") && !is_a($connection, "SimpleMock"))
		{
			trigger_error("Swift_Connection_Multi::addConnection expects parameter 1 to be instance of Swift_Connection.");
			return false;
		}
		if ($id !== null) $this->connections[$id] =& $connection;
		else $this->connections[] =& $connection;
	}
	/**
	 * Read a full response from the buffer
	 * @return string
	 * @throws Swift_Connection_Exception Upon failure to read
	 */
	function read()
	{
		if ($this->active === null)
		{
			Swift_ClassLoader::load("Swift_Connection_Exception");
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"None of the connections set have been started"));
			return;
		}
		return $this->connections[$this->active]->read();
	}
	/**
	 * Write a command to the server (leave off trailing CRLF)
	 * @param string The command to send
	 * @throws swift_Connection_Exception Upon failure to write
	 */
	function write($command, $end="\r\n")
	{
		if ($this->active === null)
		{
			Swift_ClassLoader::load("Swift_Connection_Exception");
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"None of the connections set have been started"));
			return;
		}
		return $this->connections[$this->active]->write($command, $end);
	}
	/**
	 * Try to start the connection
	 * @throws Swift_Connection_Exception Upon failure to start
	 */
	function start()
	{
		$fail_messages = array();
		foreach ($this->connections as $id => $conn)
		{
			Swift_Errors::expect($e, "Swift_Connection_Exception");
			//
				$this->connections[$id]->start();
			if (!$e) {
				if ($this->connections[$id]->isAlive())
				{
					Swift_Errors::clear("Swift_Connection_Exception");
					$this->active = $id;
					return true;
				}
				Swift_ClassLoader::load("Swift_Connection_Exception");
				Swift_Errors::trigger(new Swift_Connection_Exception(
					"The connection started but reported that it was not active"));
			}
			$fail_messages[] = $id . ": " . $e->getMessage();
			$e = null;
		}
		$failure = implode("<br />", $fail_messages);
		Swift_ClassLoader::load("Swift_Connection_Exception");
		Swift_Errors::trigger(new Swift_Connection_Exception($failure));
	}
	/**
	 * Try to close the connection
	 * @throws Swift_Connection_Exception Upon failure to close
	 */
	function stop()
	{
		if ($this->active !== null) $this->connections[$this->active]->stop();
		$this->active = null;
	}
	/**
	 * Check if the current connection is alive
	 * @return boolean
	 */
	function isAlive()
	{
		return (($this->active !== null) && $this->connections[$this->active]->isAlive());
	}
	/**
	 * Call the current connection's postConnect() method
	 */
	function postConnect(&$instance)
	{
		$this->connections[$this->active]->postConnect($instance);
	}
}
