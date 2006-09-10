<?php

$instance = null;

class Swift_Stream_Processor
{
	/**
	 * The current command being written
	 * @var string command
	 */
	var $command;
	/**
	 * The unread response, get regularly truncated from the pointer pos
	 * @var string resonse buffer
	 */
	var $response = "";
	/**
	 * Observer collection
	 * The actual logic is handled by an observer
	 * @var array observer objects
	 */
	var $observers = array();
	/**
	 * Boolean value is the handle is active
	 * @var bool stream open
	 */
	var $isOpen = false;
	/**
	 * Streams with no EOF hang indefinitely if you try to read too far
	 * We don't want that to happen in testing since it will stop the tests from completing
	 * So intead we set a value to true if it *would be* hanging, and this we can test for it
	 */
	var $hanging = false;
	
	function Swift_Stream_Processor() {}
	/**
	 * Load an observer in
	 * @param object observer
	 */
	function addObserver(&$observer)
	{
		$this->observers[] =& $observer;
	}
	/**
	 * Singleton factory
	 */
	function &getInstance()
	{
		global $instance;
		if (empty($instance)) $instance = array( new Swift_Stream_Processor() );
		return $instance[0];
	}
	/**
	 * Provide a command and store it on the buffer
	 * @param string command
	 */
	function setCommand($command)
	{
		$this->command .= $command;
		foreach ($this->observers as $i => $o) $this->observers[$i]->command($this->command);
	}
	/**
	 * Add a response to the response buffer
	 * @param string response
	 */
	function setResponse($response)
	{
		$this->response .= $response."\r\n";
	}
	/**
	 * Read the response from the response buffer
	 * Then advance the pointer
	 * @return string response
	 */
	function getResponse($size)
	{
		$ret = substr($this->response, 0, $size);
		
		$this->response = substr($this->response, $size);
		
		//Fake SMTP's behaviour of hanging past EOF
		if (!strlen($ret)) $this->hanging = true;
		else $this->hanging = false;
		
		return $ret;
	}
	/**
	 * For testing purposes we can see if the stream would be hanging in the real world
	 * @return bool hanging
	 */
	function isHanging()
	{
		return $this->hanging;
	}
	/**
	 * Kill the singleton
	 */
	function destroy()
	{
		global $instance;
		
		$instance = null;
		$this->isOpen = false;
	}
}

?>