<?php

/**
 * Swift Mailer Sendmail Connection component.
 * Please read the LICENSE file
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Connection
 * @license GNU Lesser General Public License
 */
 
require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_ConnectionBase");

if (!defined("SWIFT_SENDMAIL_AUTO_DETECT")) define("SWIFT_SENDMAIL_AUTO_DETECT", -2);

//Sorry guys, it has to be done and it's not my lazy coding, blame PHP4/proc_open()
$GLOBALS["_SWIFT_PROC"] = array();

/**
 * Swift Sendmail Connection
 * @package Swift_Connection
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Connection_Sendmail extends Swift_ConnectionBase
{
	/**
	 * Constant for auto-detection of paths
	 */
	var $AUTO_DETECT = SWIFT_SENDMAIL_AUTO_DETECT;
	/**
	 * Flags for the MTA (options such as bs or t)
	 * @var string
	 */
	var $flags = null;
	/**
	 * The full path to the MTA
	 * @var string
	 */
	var $path = null;
	/**
	 * The type of last request sent
	 * For example MAIL, RCPT, DATA
	 * @var string
	 */
	var $request = null;
	/**
	 * The process handle
	 * @var resource
	 */
	var $proc;
	/**
	 * I/O pipes for the process
	 * @var array
	 */
	var $pipes;
	/**
	 * Switches to true for just one command when DATA has been issued
	 * @var boolean
	 */
	var $send = false;
	/**
	 * The timeout in seconds before giving up
	 * @var int Seconds
	 */
	var $timeout = 10;
	
	/**
	 * Constructor
	 * @param string The command to execute
	 * @param int The timeout in seconds before giving up
	 */
	function Swift_Connection_Sendmail($command="/usr/sbin/sendmail -bs", $timeout=10)
	{
		$this->setCommand($command);
		$this->setTimeout($timeout);
	}
	/**
	 * Set the timeout on the process
	 * @param int The number of seconds
	 */
	function setTimeout($secs)
	{
		$this->timeout = (int)$secs;
	}
	/**
	 * Get the timeout on the process
	 * @return int
	 */
	function getTimeout()
	{
		return $this->timeout;
	}
	/**
	 * Set the operating flags for the MTA
	 * @param string
	 */
	function setFlags($flags)
	{
		$this->flags = $flags;
	}
	/**
	 * Get the operating flags for the MTA
	 * @return string
	 */
	function getFlags()
	{
		return $this->flags;
	}
	/**
	 * Set the path to the binary
	 * @param string The path (must be absolute!)
	 */
	function setPath($path)
	{
		if ($path == $this->AUTO_DETECT) $path = $this->findSendmail();
		$this->path = $path;
	}
	/**
	 * Get the path to the binary
	 * @return string
	 */
	function getPath()
	{
		return $this->path;
	}
	/**
	 * For auto-detection of sendmail path
	 * Thanks to "Joe Cotroneo" for providing the enhancement
	 * @return string
	 */
	function findSendmail()
	{
		$path = @trim(shell_exec('which sendmail'));
		if (!is_executable($path))
		{
			$common_locations = array(
				'/usr/bin/sendmail',
				'/usr/lib/sendmail',
				'/var/qmail/bin/sendmail',
				'/bin/sendmail',
				'/usr/sbin/sendmail',
				'/sbin/sendmail'
			);
			foreach ($common_locations as $path)
			{
				if (is_executable($path)) return $path;
			}
			//Fallback (swift will still throw an error)
			return "/usr/sbin/sendmail";
		}
		else return $path;
	}
	/**
	 * Set the sendmail command (path + flags)
	 * @param string Command
	 * @throws Swift_Connection_Exception If the command is not correctly structured
	 */
	function setCommand($command)
	{
		if ($command == $this->AUTO_DETECT) $command = $this->findSendmail() . " -bs";
        
		if (!strrpos($command, " -"))
		{
			Swift_ClassLoader::load("Swift_Connection_Exception");
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"Cannot set sendmail command with no command line flags. e.g. /usr/sbin/sendmail -t"));
			return;
		}
		$path = substr($command, 0, strrpos($command, " -"));
		$flags = substr($command, strrpos($command, " -")+2);
		$this->setPath($path);
		$this->setFlags($flags);
	}
	/**
	 * Get the sendmail command (path + flags)
	 * @return string
	 */
	function getCommand()
	{
		return $this->getPath() . " -" . $this->getFlags();
	}
	/**
	 * Write a command to the open pipe
	 * @param string The command to write
	 * @throws Swift_Connection_Exception If the pipe cannot be written to
	 */
	function pipeIn($command, $end="\r\n")
	{
		Swift_ClassLoader::load("Swift_Connection_Exception");
		if (!$this->pipes[0])
		{
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"The sendmail process is not alive and cannot be written to."));
			return;
		}
		if (!@fwrite($this->pipes[0], $command . $end))
		{
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"The sendmail process did not allow the command '" . $command . "' to be sent."));
		}
		fflush($this->pipes[0]);
	}
	/**
	 * Read data from the open pipe
	 * @return string
	 * @throws Swift_Connection_Exception If the pipe is not operating as expected
	 */
	function pipeOut()
	{
		Swift_ClassLoader::load("Swift_Connection_Exception");
		if ($this->getFlags() == "t") return;
		if (!$this->pipes[1])
		{
			Swift_Errors::trigger(new Swift_Connection_Exception("The sendmail process is not alive and cannot be read from."));
			return;
		}
		$ret = "";
		$line = 0;
		while (true)
		{
			$line++;
			stream_set_timeout($this->pipes[1], $this->timeout);
			$tmp = @fgets($this->pipes[1]);
			if ($tmp === false)
			{
				Swift_Errors::trigger(new Swift_Connection_Exception(
					"There was a problem reading line " . $line . " of a sendmail SMTP response. The response so far was:<br />" . $ret));
				return;
			}
			$ret .= trim($tmp) . "\r\n";
			if ($tmp{3} == " ") break;
		}
		fflush($this->pipes[1]);
		return $ret = substr($ret, 0, -2);
	}
	/**
	 * Read a full response from the buffer (this is spoofed if running in -t mode)
	 * @return string
	 * @throws Swift_Connection_Exception Upon failure to read
	 */
	function read()
	{
		if ($this->getFlags() == "t")
		{
			switch (strtolower($this->request))
			{
				case null:
					return "220 Greetings";
				case "helo": case "ehlo":
					return "250 hello";
				case "mail": case "rcpt": case "rset": case "quit":
					return "250 ok";
				case "data":
					$this->send = true;
					return "354 go ahead";
				default:
					return "250 ok";
			}
		}
		else return $this->pipeOut();
	}
	/**
	 * Write a command to the process (leave off trailing CRLF)
	 * @param string The command to send
	 * @throws Swift_Connection_Exception Upon failure to write
	 */
	function write($command, $end="\r\n")
	{
		if ($this->getFlags() == "t")
		{
			if (!$this->send && strpos($command, " ")) $command = substr($command, strpos($command, " ")+1);
			elseif ($this->send)
			{
				$this->pipeIn($command, $end);
			}
			$this->request = $command;
			$this->send = (strtolower($command) == "data");
		}
		else $this->pipeIn($command);
	}
	/**
	 * Try to start the connection
	 * @throws Swift_Connection_Exception Upon failure to start
	 */
	function start()
	{
		if (!$this->getPath() || !$this->getFlags())
		{
			Swift_ClassLoader::load("Swift_Connection_Exception");
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"Sendmail cannot be started without a path to the binary including flags."));
			return;
		}
			
		$pipes_spec = array(
			array("pipe", "r"),
			array("pipe", "w"),
			array("pipe", "w")
		);
		
		$i = count($GLOBALS["_SWIFT_PROC"]);
		$GLOBALS["_SWIFT_PROC"][$i] = proc_open($this->getCommand(), $pipes_spec, $this->pipes);
		$this->proc =& $GLOBALS["_SWIFT_PROC"][$i];
		
		if (!$this->proc)
		{
			Swift_ClassLoader::load("Swift_Connection_Exception");
			Swift_Errors::trigger(new Swift_Connection_Exception(
				"The sendmail process failed to start.  Please verify that the path exists and PHP has permission to execute it."));
			return;
		}
	}
	/**
	 * Try to close the connection
	 * @throws Swift_Connection_Exception Upon failure to close
	 */
	function stop()
	{
		foreach ($this->pipes as $pipe)
		{
			if (!@fclose($pipe))
			{
				Swift_ClassLoader::load("Swift_Connection_Exception");
				Swift_Errors::trigger(new Swift_Connection_Exception("The open sendmail process is failing to close."));
				return;
			}
		}
		
		if ($this->proc)
		{
			proc_close($this->proc);
			$this->pipes = null;
			$this->proc = null;
		}
	}
	/**
	 * Check if the process is still alive
	 * @return boolean
	 */
	function isAlive()
	{
		return ($this->proc !== null);
	}
}
