<?php

/**
 * Swift Mailer: A Flexible PHP Mailer Class.
 *
 * Current functionality:
 *  
 *  * Send uses one single connection to the SMTP server
 *  * Doesn't rely on mail()
 *  * Custom Headers
 *  * Unlimited redundant connections (can be mixed type)
 *  * Connection cycling & load balancing
 *  * Sends Multipart messages, handles encoding
 *  * Sends Plain-text single-part emails
 *  * Fast Cc and Bcc handling
 *  * Immune to rejected recipients (sends to subsequent recipients w/out error)
 *  * Set Priority Level
 *  * Request Read Receipts
 *  * Unicode UTF-8 support with auto-detection
 *  * Auto-detection of SMTP/Sendmail details based on PHP & server configuration
 *  * Batch emailing with multiple To's or without
 *  * Support for multiple attachments
 *  * Sendmail (or other binary) support
 *  * Pluggable SMTP Authentication (LOGIN, PLAIN, MD5-CRAM, POP Before SMTP)
 *  * Secure Socket Layer connections (SSL)
 *  * Transport Layer security (TLS) - Gmail account holders!
 *  * Send mail with inline embedded images easily (or embed other file types)!
 *  * Loadable plugin support with event handling features
 *
 * @package	Swift
 * @version	2.1.16-php4
 * @author	Chris Corbyn
 * @date	9th September 2006
 * @license http://www.gnu.org/licenses/lgpl.txt Lesser GNU Public License
 *
 * @copyright Copyright &copy; 2006 Chris Corbyn - All Rights Reserved.
 * @filesource
 *
 * -----------------------------------------------------------------------
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 2.1 of the License, or any later version.
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

if (!defined('SWIFT_VERSION')) define('SWIFT_VERSION', '2.1.16');

/**
 * Swift Mailer Class.
 * Accepts connections to an MTA and deals with the sending and processing of
 * commands and responses.
 * @package	Swift
 */
class Swift
{
	/**
	 * Plugins container
	 * @var  array  plugins
	 * @private
	 */
	var $plugins = array();
	var $esmtp = false;
	var $_8bitmime = false;
	var $autoCompliance = false;
	/**
	 * Whether or not Swift should send unique emails to all "To"
	 * recipients or just bulk them together in the To header.
	 * @var bool use_exact
	 */
	var $useExactCopy = false;
	var $domain = 'SwiftUser';
	var $mimeBoundary;
	var $mimeWarning;
	/**
	 * MIME Parts container
	 * @var  array  parts
	 * @private
	 */
	var $parts = array();
	/**
	 * Attachment data container
	 * @var  array  attachments
	 * @private
	 */
	var $attachments = array();
	/**
	 * Inline image container
	 * @var  array  image parts
	 * @private
	 */
	var $images = array();
	/**
	 * Response codes expected for commands
	 * $command => $code
	 * @var  array  codes
	 * @private
	 */
	var $expectedCodes = array(
		'ehlo' => 250,
		'helo' => 250,
		'mail' => 250,
		'rcpt' => 250,
		'data' => 354
	);
	/**
	 * Blind-carbon-copy address container
	 * @var array addresses
	 */
	var $Bcc = array();
	/**
	 * Carbon-copy address container
	 * @var array addresses
	 */
	var $Cc = array();
	/**
	 * The address any replies will go to
	 * @var string address
	 */
	var $replyTo;
	/**
	 * The addresses we're sending to
	 * @var string address
	 */
	var $to = array();
	/**
	 * The sender of the email
	 * @var string sender
	 */
	var $from;
	/**
	 * Priority value 1 (high) to 5 (low)
	 * @var int priority (1-5)
	 */
	var $priority = 3;
	/**
	 * Whether a read-receipt is required
	 * @var bool read receipt
	 */
	var $readReceipt = false;
	/**
	 * The max number of entires that can exist in the log
	 * (saves memory)
	 * @var int log size
	 */
	var $maxLogSize = 30;
	/**
	 * The address to which bounces are sent
	 * @var string Return-Path:
	 */
	var $returnPath;
	
	/**
	 * Connection object (container holding a socket)
	 * @var  object  connection
	 */
	var $connection;
	/**
	 * Authenticators container
	 * @var  array  authenticators
	 */
	var $authenticators = array();
	var $authTypes = array();
	/**
	 * Holds the username used in authentication (if any)
	 * @var string username
	 */
	var $username;
	/**
	 * Holds the password used in authentication (if any)
	 * @var string password
	 */
	var $password;
	
	var $charset = "ISO-8859-1";
	var $userCharset = false;
	/**
	 * Boolean value representing if Swift has failed or not
	 * @var  bool  failed
	 */
	var $failed = false;
	/**
	 * If Swift should clear headers etc automatically
	 * @var bool autoFlush
	 */
	var $autoFlush = true;
	/**
	 * Numeric code from the last MTA response
	 * @var  int  code
	 */
	var $responseCode;
	/**
	 * Keyword of the command being sent
	 * @var string keyword
	 */
	var $commandKeyword;
	/**
	 * Last email sent or email about to be sent (dependant on location)
	 * @var  array  commands
	 */
	var $currentMail = array();
	/**
	 * Email headers
	 * @var  string  headers
	 */
	var $headers;
	var $currentCommand = '';
	/**
	 * Errors container
	 * @var  array  errors
	 */
	var $errors = array();
	/**
	 * Log container
	 * @var  array  transactions
	 */
	var $transactions = array();
	
	var $lastTransaction;
	var $lastError;
	/**
	 * The very most recent response received from the MTA
	 * @var  string  response
	 */
	var $lastResponse;
	/**
	 * The total number of failed recipients
	 * @var int failed
	 */
	var $failCount = 0;
	/**
	 * Number of failed recipients for this email
	 * @var int failed
	 */
	var $subFailCount = 0;
	/**
	 * Number of addresses expected to pass this email
	 * @var int recipients
	 */
	var $numAddresses;
	/**
	 * Container for any recipients rejected
	 * @var array failed addresses
	 */
	var $failedAddresses = array();
	/**
	 * Number of commands which will be skipped
	 */
	var $ignoreCommands = 0;
	/**
	 * Number of commands skipped thus far
	 */
	var $skippedCommands = 0;
	
	/**
	 * Swift Constructor
	 * @param  object  Swift_IConnection
	 * @param  string  user_domain, optional
	 */
	function Swift(&$object, $domain=false)
	{
		if (!$domain) $domain = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'SwiftUser';
		
		$this->domain = $domain;
		$this->connection =& $object;

		$this->connect();
		// * Hey this library is FREE so it's not much to ask ;)  But if you really do want to
		// remove this header then go ahead of course... what's GPL for? :P
		$this->headers = "X-Mailer: Swift ".SWIFT_VERSION." by Chris Corbyn\r\n";
		$this->mimeWarning = "This part of the E-mail should never be seen. If\r\n".
		"you are reading this, consider upgrading your e-mail\r\n".
		"client to a MIME-compatible client.";
	}
	/**
	 * Connect to the server
	 * @return bool connected
	 */
	function connect()
	{
		if (!$this->connection->start())
		{
			$this->fail();
			$error = 'Connection to the given MTA failed.';
			if (!empty($this->connection->error)) $error .= ' The Connection Interface said: '.$this->connection->error;
			$this->logError($error, 0);
			return false;
		}
		else
		{
			$this->handshake();
			return true;
		}
	}
	/**
	 * Returns TRUE if the connection is active.
	 */
	function isConnected()
	{
		return $this->connection->isConnected();
	}
	/**
	 * Sends the standard polite greetings to the MTA and then
	 * identifies the MTA's capabilities
	 */
	function handshake()
	{
		$this->commandKeyword = "";
		//What did the server greet us with on connect?
		$this->logTransaction();
		if ($this->supportsESMTP($this->lastResponse))
		{
			//Just being polite
			$list = $this->command("EHLO {$this->domain}\r\n");
			$this->check8BitMime($this->lastResponse);
			
			$this->getAuthenticationMethods($list);
			
			$this->esmtp = true;
		}
		else $this->command("HELO {$this->domain}\r\n");
	}
	/**
	 * Check if the server allows 8bit emails to be sent without quoted-printable encoding
	 * @param string EHLO response
	 */
	function check8BitMime($string)
	{
		if (strpos($string, '8BITMIME')) $this->_8bitmime = true;
	}
	/**
	 * Checks for Extended SMTP support
	 * @param  string  MTA greeting
	 * @return  bool  ESMTP
	 * @private
	 */
	function supportsESMTP($greeting)
	{
		//Not mentiioned in RFC 2821 but this how it's done
		if (strpos($greeting, 'ESMTP')) return true;
		else return false;
	}
	/**
	 * Set the maximum num ber of entries in the log
	 * @param int size
	 */
	function setMaxLogSize($size)
	{
		$this->maxLogSize = (int) $size;
	}
	/**
	 * Sets the priority level of the email
	 * This must be 1 to 5 where 1 is highest
	 * @param int priority
	 */
	function setPriority($level)
	{
		$level = (int) $level;
		if ($level < 1) $level = 1;
		if ($level > 5) $level = 5;
		switch ($level)
		{
			case 1: case 2:
			$this->addHeaders("X-Priority: $level\r\nX-MSMail-Priority: High");
			break;
			case 4: case 5:
			$this->addHeaders("X-Priority: $level\r\nX-MSMail-Priority: Low");
			break;
			case 3: default:
			$this->addHeaders("X-Priority: $level\r\nX-MSMail-Priority: Normal");
		}
	}
	/**
	 * Set the return path address (Bounce detection)
	 * @param string address
	 */
	function setReturnPath($address)
	{
		$this->returnPath = $this->getAddress($address);
	}
	/**
	 * Request a read receipt from all recipients
	 * @param bool request receipt
	 */
	function requestReadReceipt($request=true)
	{
		$this->readReceipt = (bool) $request;
	}
	/**
	 * Set the character encoding were using
	 * @param string charset
	 */
	function setCharset($string)
	{
		$this->charset = $string;
		$this->userCharset = $string;
	}
	/**
	 * Whether or not Swift should send unique emails to all To recipients
	 * @param bool unique
	 */
	function useExactCopy($use=true)
	{
		$this->useExactCopy = (bool) $use;
	}
	/**
	 * Get the return path recipient
	 */
	function getReturnPath()
	{
		return $this->returnPath;
	}
	/**
	 * Get the sender
	 */
	function getFromAddress()
	{
		return $this->from;
	}
	/**
	 * Get Cc recipients
	 */
	function getCcAddresses()
	{
		return $this->Cc;
	}
	/**
	 * Get Bcc addresses
	 */
	function getBccAddresses()
	{
		return $this->Bcc;
	}
	/**
	 * Get To addresses
	 */
	function getToAddresses()
	{
		return $this->to;
	}
	/**
	 * Get the list of failed recipients
	 * @return array recipients
	 */
	function getFailedRecipients()
	{
		return $this->failedAddresses;
	}
	/**
	 * Return the array of errors (if any)
	 * @return array errors
	 */
	function getErrors()
	{
		return $this->errors;
	}
	/**
	 * Return the conversation up to maxLogSize between the SMTP server and swift
	 * @return array transactions
	 */
	function getTransactions()
	{
		return $this->transactions;
	}
	/**
	 * Sets the Reply-To address used for sending mail
	 * @param string address
	 */
	function setReplyTo($string)
	{
		$this->replyTo = $this->getAddress($string);
	}
	/**
	 * Add one or more Blind-carbon-copy recipients to the mail
	 * @param mixed addresses
	 */
	function addBcc($addresses)
	{
		$this->Bcc = array_merge($this->Bcc, $this->parseAddressList((array) $addresses));
	}
	/**
	 * Add one or more Carbon-copy recipients to the mail
	 * @param mixed addresses
	 */
	function addCc($addresses)
	{
		$this->Cc = array_merge($this->Cc, $this->parseAddressList((array) $addresses));
	}
	/**
	 * Force swift to break lines longer than 76 characters long
	 * @param  bool  resize
	 */
	function useAutoLineResizing($use=true)
	{
		$this->autoCompliance = (bool) $use;
	}
	/**
	 * Associate a code with a command. Swift will fail quietly if the code
	 * returned does not match.
	 * @param  string  command
	 * @param  int  code
	 */
	function addExpectedCode($command, $code)
	{
		$this->expectedCodes[$command] = (int) $code;
	}
	/**
	 * Reads the EHLO return string to see what AUTH methods are supported
	 * @param  string  EHLO response
	 * @return  void
	 * @private
	 */
	function getAuthenticationMethods($list)
	{
		preg_match("/^250[\-\ ]AUTH\ (.*)\r\n/m", $list, $matches);
		if (!empty($matches[1]))
		{
			$types = explode(' ', $matches[1]);
			$this->authTypes = $types;
		}
	}
	/**
	 * Load a plugin object into Swift
	 * @param  object  Swift_IPlugin
	 * @param string plugin name
	 * @return  void
	 */
	function loadPlugin(&$object, $id=false)
	{
		if ($id) $object->pluginName = $id;
		$this->plugins[$object->pluginName] =& $object;
		$this->plugins[$object->pluginName]->loadBaseObject($this);

		if (method_exists($this->plugins[$object->pluginName], 'onLoad'))
		{
			$this->plugins[$object->pluginName]->onLoad();
		}
	}
	/**
	 * Fetch a reference to a plugin in Swift
	 * @param  string  plugin name
	 * @return  object  Swift_IPlugin
	 */
	function &getPlugin($name)
	{
		if (isset($this->plugins[$name]))
		{
			return $this->plugins[$name];
		}
	}
	/**
	 * Un-plug a loaded plugin. Returns false on failure.
	 * @param string plugin_name
	 * @return bool success
	 */
	function removePlugin($name)
	{
		if (!isset($this->plugins[$name])) return false;
		
		if (method_exists($this->plugins[$name], 'onUnload'))
		{
			$this->plugins[$name]->onUnload();
		}
		unset($this->plugins[$name]);
		return true;
	}
	/**
	 * Return the number of plugins loaded
	 * @return int plugins
	 */
	function numPlugins()
	{
		return count($this->plugins);
	}
	/**
	 * Trigger event handlers
	 * @param  string  event handler
	 * @return  void
	 * @private
	 */
	function triggerEventHandler($func)
	{
		foreach ($this->plugins as $name => $object)
		{
			if (method_exists($this->plugins[$name], $func))
			{
				$this->plugins[$name]->$func();
			}
		}
	}
	/**
	 * Attempt to load any authenticators from the Swift/ directory
	 * @see  RFC 2554
	 * @return  void
	 * @private
	 */
	function loadDefaultAuthenticators()
	{
		$dir = dirname(__FILE__).'/Swift/Authenticator';
		if (file_exists($dir) && is_dir($dir))
		{
			$handle = opendir($dir);
			while ($file = readdir($handle))
			{
				if (preg_match('@^([a-zA-Z\d]*)\.php$@', $file, $matches))
				{
					require_once($dir.'/'.$file);
					$class = 'Swift_Authenticator_'.$matches[1];
					$this->loadAuthenticator(new $class);
				}
			}
			closedir($handle);
		}
	}
	/**
	 * Use SMTP authentication
	 * @param  string  username
	 * @param  string  password
	 * @return  bool  successful
	 */
	function authenticate($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	
		if (empty($this->authenticators)) $this->loadDefaultAuthenticators();
		
		if (!$this->esmtp || empty($this->authTypes))
		{
			$this->logError('The MTA doesn\'t support any of Swift\'s loaded authentication mechanisms', 0);
			return false;
		}
		foreach ($this->authenticators as $name => $object)
		{
			//An asterisk means that the auth type is not advertised by ESMTP
			if (in_array($name, $this->authTypes) || substr($name, 0, 1) == '*')
			{
				if ($this->authenticators[$name]->run($username, $password))
				{
					$this->triggerEventHandler('onAuthenticate');
					return true;
				}
				else return false;
			}
		}
		//If we get this far, no authenticators were used
		$this->logError('The MTA doesn\'t support any of Swift\'s loaded authentication mechanisms', 0);
		$this->fail();
		return false;
	}
	/**
	 * Load an authentication mechanism object into Swift
	 * @param  object  Swift_IAuthenticator
	 * @return  void
	 */
	function loadAuthenticator(&$object)
	{
		$this->authenticators[$object->serverString] =& $object;
		$this->authenticators[$object->serverString]->loadBaseObject($this);
	}
	/**
	 * Get a unique multipart MIME boundary
	 * @param  string  mail data, optional
	 * @return  string  boundary
	 * @private
	 */
	function getMimeBoundary($string=false)
	{
		$force = true;
		if (!$string)
		{
			$force = false;
			$string = implode('', $this->parts);
			$string .= implode('', $this->attachments);
		}
		if ($this->mimeBoundary && !$force) return $this->mimeBoundary;
		else
		{ //Make sure we don't (as if it would ever happen!) -
		  // produce a boundary that's actually in the email already
			do
			{
				$this->mimeBoundary = '_=_swift-'.uniqid(rand(), true);
			} while(strpos($string, $this->mimeBoundary));
		}
		return $this->mimeBoundary;
	}
	/**
	 * Append a string to the message header
	 * @param  string  headers
	 * @return  void
	 */
	function addHeaders($string)
	{
		$this->headers .= preg_replace("/(?:\r|\n|^)[^:]*?:\ *(.*?)(?:\r|\n|$)/me", 'str_replace("$1", $this->safeEncodeHeader("$1"), "$0")', $string);
		if (substr($this->headers, -2) != "\r\n")
			$this->headers .= "\r\n";
	}
	/**
	 * Set the multipart MIME boundary (only works for first part)
	 * @param  string  boundary
	 * @return  void
	 */
	function setMimeBoundary($string)
	{
		$this->mimeBoundary = $string;
	}
	/**
	 * Set the text that displays in non-MIME clients
	 * @param  string  warning
	 * @return  void
	 */
	function setMimeWarning($warning)
	{
		$this->mimeWarning = $warning;
	}
	/**
	 * Tells Swift to clear out attachment, parts, headers etc
	 * automatically upon sending - this is the default.
	 * @param bool flush
	 */
	function autoFlush($flush=true)
	{
		$this->autoFlush = (bool) $flush;
	}
	/**
	 * Empty out the MIME parts and attachments
	 * @param  bool  reset headers
	 * @return  void
	 */
	function flush($clear_headers=false)
	{
		$this->parts = array();
		$this->attachments = array();
		$this->images = array();
		$this->mimeBoundary = null;
		$this->Bcc = array();
		$this->to = array();
		$this->Cc = array();
		$this->replyTo = null;
		//See comment above the headers property above the constructor before editing this line! *
		if ($clear_headers) $this->headers = "X-Mailer: Swift ".SWIFT_VERSION." by Chris Corbyn\r\n";
		$this->triggerEventHandler('onFlush');
	}
	/**
	 * Reset to
	 */
	function flushTo()
	{
		$this->to = array();
	}
	/**
	 * Reset Cc
	 */
	function flushCc()
	{
		$this->Cc = array();
	}
	/**
	 * Reset Bcc
	 */
	function flushBcc()
	{
		$this->Bcc = array();
	}
	/**
	 * Reset parts
	 */
	function flushParts()
	{
		$this->parts = array();
		$this->images = array();
	}
	/**
	 * Reset attachments
	 */
	function flushAttachments()
	{
		$this->attachments = array();
	}
	/**
	 * Reset headers
	 */
	function flushHeaders()
	{
		$this->headers = "X-Mailer: Swift ".SWIFT_VERSION." by Chris Corbyn\r\n";
	}
	/**
	 * Log an error in Swift::errors
	 * @param  string  error string
	 * @param  int  error number
	 * @return  void
	 */
	function logError($errstr, $errno=0)
	{
		$this->errors[] = array(
			'num' => $errno,
			'time' => microtime(),
			'message' => $errstr
		);
		$this->lastError = $errstr;
		
		$this->triggerEventHandler('onError');
	}
	/**
	 * Log a transaction in Swift::transactions
	 * @param  string  command
	 * @return  void
	 */
	function logTransaction($command='')
	{
		$this->lastTransaction = array(
			'command' => $command,
			'time' => microtime(),
			'response' => $this->getResponse()
		);
		$this->triggerEventHandler('onLog');
		if ($this->maxLogSize)
		{
			$this->transactions = array_slice(array_merge($this->transactions, array($this->lastTransaction)), -$this->maxLogSize);
		}
		else $this->transactions[] = $this->lastTransaction;
	}
	/**
	 * Read the data from the socket
	 * @return  string  response
	 * @private
	 */
	function getResponse()
	{
		if (!$this->connection->readHook || !$this->isConnected()) return false;
		$ret = "";
		while (true)
		{
			$tmp = @fgets($this->connection->readHook);
			$ret .= $tmp;
			//The last line of SMTP replies have a space after the status number
			// They do NOT have an EOF so while(!feof($socket)) will hang!
			if (substr($tmp, 3, 1) == ' ' || $tmp == false) break;
		}
		$this->responseCode = $this->getResponseCode($ret);
		$this->lastResponse = $ret;
		$this->triggerEventHandler('onResponse');
		return $this->lastResponse;
	}
	/**
	 * Get the number of the last server response
	 * @param  string  response string
	 * @return  int  response code
	 * @private
	 */
	function getResponseCode($string)
	{
		return (int) sprintf("%d", $string);
	}
	/**
	 * Get the first word of the command
	 * @param  string  command
	 * @return  string  keyword
	 * @private
	 */
	function getCommandKeyword($comm)
	{
		if (false !== $pos = strpos($comm, ' '))
		{
			return $this->commandKeyword = strtolower(substr($comm, 0, $pos));
		}
		else return $this->commandKeyword = strtolower(trim($comm));
	}
	/**
	 * Send a reset command in the event of a problem
	 */
	function reset()
	{
		$this->command("RSET\r\n");
	}
	/**
	 * Issue a command to the socket
	 * @param  string  command
	 * @return  string  response
	 */
	function command($comm)
	{
		//We'll usually ignore a certain sequence of commands if something screwed up
		if ($this->ignoreCommands)
		{
			$this->skippedCommands++;
			if ($this->skippedCommands >= $this->ignoreCommands)
			{
				$this->responseCode = -2; //Done (internal to swift)
				$this->ignoreCommands = 0;
				$this->skippedCommands = 0;
			}
			return true;
		}
		
		$this->currentCommand = ltrim($comm);
		
		$this->triggerEventHandler('onBeforeCommand');
		
		if (!$this->connection->writeHook || !$this->isConnected() || $this->failed)
		{
			$this->logError('Error running command: '.trim($comm).'.  No connection available', 0);
			return false;
		}

		$command_keyword = $this->getCommandKeyword($this->currentCommand);
		
		//We successfully got as far as asking to send the email so we can forget any failed addresses for now
		if ($command_keyword != 'rcpt' && $command_keyword != 'rset') $this->subFailCount = 0;
		
		//SMTP commands must end with CRLF
		if (substr($this->currentCommand, -2) != "\r\n") $this->currentCommand .= "\r\n";
		
		if (@fwrite($this->connection->writeHook, $this->currentCommand))
		{
			$this->logTransaction($this->currentCommand);
			if (array_key_exists($command_keyword, $this->expectedCodes))
			{
				if ($this->expectedCodes[$command_keyword] != $this->responseCode)
				{
					//If a recipient was rejected
					if ($command_keyword == 'rcpt')
					{
						$this->failCount++;
						$this->failedAddresses[] = $this->getAddress($comm);
						//Some addresses may still work...
						if (++$this->subFailCount >= $this->numAddresses)
						{
							//Sending failed, just RSET and don't send data to this recipient
							$this->reset();
							//So we can still cache the mail body in send()
							$this->responseCode = -1; //Pending (internal to swift)
							//Skip the next two commands (DATA and <mail>)
							$this->ignoreCommands = 2;
							$this->logError('Send Error: Sending to '.$this->subFailCount.' recipients rejected (bad response code).', $this->responseCode);
							//But don't fail here.... these are usually not fatal
						}
					}
					else
					{
						$this->fail();
						$this->logError('MTA Error (Swift was expecting response code '.$this->expectedCodes[$command_keyword].' but got '.$this->responseCode.'): '.$this->lastResponse, $this->responseCode);
						return $this->hasFailed();
					}
				}
			}
			$this->triggerEventHandler('onCommand');
			return $this->lastResponse;
		}
		else return false;
	}
	/**
	 * Splits lines longer than 76 characters to multiple lines
	 * @param  string  text
	 * @return  string chunked output
	 */
	function chunkSplitLines($string)
	{
		return wordwrap($string, 74, "\r\n");
	}
	/**
	 * Add a part to a multipart message
	 * @param  string  body
	 * @param  string  content-type, optional
	 * @param  string  content-transfer-encoding, optional
	 * @return  void
	 */
	function addPart($string, $type='text/plain', $encoding=false)
	{
		if (!$this->userCharset && (strtoupper($this->charset) != 'UTF-8') && $this->detectUTF8($string)) $this->charset = 'UTF-8';
		
		if (!$encoding && $this->_8bitmime) $encoding = '8bit';
		elseif (!$encoding) $encoding = 'quoted-printable';
		
		$body_string = $this->encode($string, $encoding);
		if ($this->autoCompliance && $encoding != 'binary') $body_string = $this->chunkSplitLines($body_string);
		$ret = "Content-Type: $type; charset=\"{$this->charset}\"; format=flowed\r\n".
				"Content-Transfer-Encoding: $encoding\r\n\r\n".
				$body_string;
		
		if (strtolower($type) == 'text/html') $this->parts[] = $this->makeSafe($ret);
		else $this->parts = array_merge((array) $this->makeSafe($ret), $this->parts);
	}
	/**
	 * Get the current number of parts in the email
	 * @return int num parts
	 */
	function numParts()
	{
		return count($this->parts);
	}
	/**
	 * Add an attachment to a multipart message.
	 * Attachments are added as base64 encoded data.
	 * @param  string  data
	 * @param  string  filename
	 * @param  string  content-type
	 * @return  void
	 */
	function addAttachment($data, $filename, $type='application/octet-stream')
	{
		$this->attachments[] = "Content-Type: $type; ".
				"name=\"$filename\";\r\n".
				"Content-Transfer-Encoding: base64\r\n".
				"Content-Description: $filename\r\n".
				"Content-Disposition: attachment; ".
				"filename=\"$filename\"\r\n\r\n".
				chunk_split($this->encode($data, 'base64'));
	}
	/**
	 * Get the current number of attachments in the mail
	 * @return int num attachments
	 */
	function numAttachments()
	{
		return count($this->attachments);
	}
	/**
	 * Insert an inline image and return it's name
	 * These work like attachments but have a content-id
	 * and are inline/related.
	 * @param string path
	 * @return string name
	 */
	function addImage($path)
	{
		if (!file_exists($path)) return false;
		
		$gpc = ini_get('magic_quotes_gpc');
		ini_set('magic_quotes_gpc', 0);
		$gpc_run = ini_get('magic_quotes_runtime');
		ini_set('magic_quotes_runtime', 0);
		
		$img_data = @getimagesize($path);
		if (!$img_data) return false;
		
		$type = image_type_to_mime_type($img_data[2]);
		$filename = basename($path);
		$data = file_get_contents($path);
		$cid = 'SWM'.md5(uniqid(rand(), true));
		
		$this->images[] = "Content-Type: $type\r\n".
				"Content-Transfer-Encoding: base64\r\n".
				"Content-Disposition: inline; ".
				"filename=\"$filename\"\r\n".
				"Content-ID: <$cid>\r\n\r\n".
				chunk_split($this->encode($data, 'base64'));
		
		ini_set('magic_quotes_gpc', $gpc);
		ini_set('magic_quotes_runtime', $gpc_run);
		
		return 'cid:'.$cid;
	}
	/**
	 * Insert an inline file and return it's name
	 * These work like attachments but have a content-id
	 * and are inline/related.
	 * The data is the file contents itself (binary safe)
	 * @param string file contents
	 * @param string content-type
	 * @param string filename
	 * @param string content-id
	 * @return string name
	 */
	function embedFile($data, $type='application/octet-stream', $filename=false, $cid=false)
	{
		if (!$cid) $cid = 'SWM'.md5(uniqid(rand(), true));
		
		if (!$filename) $filename = $cid;
		
		$this->images[] = "Content-Type: $type\r\n".
				"Content-Transfer-Encoding: base64\r\n".
				"Content-Disposition: inline; ".
				"filename=\"$filename\"\r\n".
				"Content-ID: <$cid>\r\n\r\n".
				chunk_split($this->encode($data, 'base64'));
		
		return 'cid:'.$cid;
	}
	/**
	 * Close the connection in the connecion object
	 * @return  void
	 */
	function close()
	{
		if ($this->connection->writeHook && $this->isConnected())
		{
			$this->command("QUIT\r\n");
			$this->connection->stop();
		}
		$this->triggerEventHandler('onClose');
	}
	/**
	 * Check if Swift has failed and stopped processing
	 * @return  bool  failed
	 */
	function hasFailed()
	{
		return $this->failed;
	}
	/**
	 * Force Swift to fail and stop processing
	 * @return  void
	 */
	function fail()
	{
		$this->failed = true;
		$this->triggerEventHandler('onFail');
	}
	/**
	 * Detect if a string contains multi-byte non-ascii chars that fall in the UTF-8 tanges
	 * @param mixed input
	 * @return bool
	 */
	function detectUTF8($string_in)
	{
		foreach ((array)$string_in as $string)
		{
			if (preg_match('%(?:
			[\xC2-\xDF][\x80-\xBF]				# non-overlong 2-byte
			|\xE0[\xA0-\xBF][\x80-\xBF]			# excluding overlongs
			|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|\xED[\x80-\x9F][\x80-\xBF]			# excluding surrogates
			|\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|[\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
			|\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
			)+%xs', $string)) return true;
		}
		return false;
	}
	/**
	 * This function checks for 7bit *printable* characters
	 * which excludes \r \n \t etc and so, is safe for use in mail headers
	 * Actual permitted chars [\ !"#\$%&'\(\)\*\+,-\.\/0123456789:;<=>\?@ABCDEFGHIJKLMNOPQRSTUVWXYZ\[\\\]\^_`abcdefghijklmnopqrstuvwxyz{\|}~]
	 * Ranges \x00-\x1F are printer control sequences
	 * \x7F is the ascii delete character
	 * @param string input
	 * @return bool
	 */
	function is7bitPrintable($string)
	{
		if (preg_match('/^[\x20-\x7E]*$/D', $string)) return true;
		else return false;
	}
	/**
	 * This is harsh! It makes things safe for sending as command sequences in SMTP
	 * Specifically the enveloping.  We could be extremely strict and implement what I planned on doing
	 * here: http://www.swiftmailer.org/contrib/proposed-address-test.txt
	 * @param string input
	 * @return safe output
	 */
	function make7bitPrintable($string)
	{
		return preg_replace('/[^\x20-\x7E]/', '', $string);
	}
	/**
	 * Encode a string (mail) in a given format
	 * Currently supports:
	 *  - BASE64
	 *  - Quoted-Printable
	 *  - Ascii 7-bit
	 *  - Ascii 8bit
	 *  - Binary (not encoded)
	 *
	 * @param  string  input
	 * @param  string  encoding
	 * @return  string  encoded output
	 */
	function encode($string, $type, $maxlen=false)
	{
		$type = strtolower($type);
		
		switch ($type)
		{
			case 'base64':
			return base64_encode($string);
			break;
			//
			case 'quoted-printable':
			return $this->quotedPrintableEncode($string, $maxlen);
			//
			case '7bit':
			case '8bit':
			break;
			case 'binary':
			default:
			break;
		}
		
		return $string;
	}
	/**
	 * Headers cannot have non-ascii or high ascii chars in them
	 * @param mixed input
	 * @return string encoded output (with encoding type)
	 */
	function safeEncodeHeader($string)
	{
		if (!is_array($string))
		{
			if ($this->is7bitPrintable($string)) return $string;
			else
			{
				//Check if the string contains address notation
				$address_start = strrpos($string, '<');
				$address_end = strrpos($string, '>');
				$address = '';
				//If the < and > are in the correct places
				if (($address_start !== false) && $address_start < $address_end)
				{
					//Then store the email address
					$address = substr($string, $address_start, ($address_end-$address_start+1));
					if (!$this->is7bitPrintable($address)) $address = $this->make7bitPrintable($address);
					//... and remove it from the string
					$string = substr($string, 0, $address_start);
				}
				$encoded = trim(chunk_split($this->encode($string, 'base64')));
				$lines = explode("\r\n", $encoded);
				return  '=?'.$this->charset.'?B?'.implode("?=\r\n =?{$this->charset}?B?", $lines).'?= '.$address;
			}
		}
		else
		{
			$ret = array();
			foreach ($string as $line)
			{
				$ret[] = $this->safeEncodeHeader($line); //Recurse
			}
			return $ret;
		}
	}
	/**
	 * Handles quoted-printable encoding
	 * From php.net by user bendi at interia dot pl
	 * @param  string  input
	 * @param int maxlength
	 * @return  string  encoded output
	 * @private
	 */
	function quotedPrintableEncode($string, $maxlen=false)
	{
		if (!$maxlen) $maxlen = 73;
		$string = preg_replace('/[^\x21-\x3C\x3E-\x7E\x09\x20]/e', 'sprintf( "=%02x", ord ( "$0" ) ) ;', $string);
		preg_match_all('/.{1,'.$maxlen.'}([^=]{0,3})?/', $string, $matches);
		$sep = "=\r\n";
		return implode($sep, $matches[0]);
	}
	/**
	 * Converts lone LF characters to CRLF
	 * @param  string  input
	 * @return  string  converted output
	 */
	function LFtoCRLF($string)
	{
		return preg_replace("@(?:(?<!\r)\n)|(?:\r(?!\n))@", "\r\n", $string);
	}
	/**
	 * Prevents premature <CRLF>.<CRLF> strings
	 * Converts any lone LF characters to CRLF
	 * @param  string  input
	 * @return  string  escaped output
	 */
	function makeSafe($string)
	{
		return str_replace("\r\n.", "\r\n..", $this->LFtoCRLF($string));
	}
	/**
	 * Pulls an email address from a "Name" <add@ress> string
	 * @param string input
	 * @return string address
	 */
	function getAddress($string)
	{
		if (!$string) return null;
		
		if (preg_match('/^.*?<([^>]+)>\s*$/s', $string, $matches))
		{
			return '<'.$this->make7bitPrintable($matches[1]).'>';
		}
		elseif (!preg_match('/<|>/', $string)) return '<'.$this->make7bitPrintable($string).'>';
		else return $this->make7bitPrintable($string);
	}
	/**
	 * Builds the headers needed to reflect who the mail is sent to
	 * Presently this is just the "To: " header
	 * @param  string  address
	 * @return  string  headers
	 * @private
	 */
	function makeRecipientHeaders($address=false)
	{
		if ($address) return "To: ".$this->safeEncodeHeader($address)."\r\n";
		else
		{
			$ret = "To: ".implode(",\r\n\t", $this->safeEncodeHeader($this->to))."\r\n";
			if (!empty($this->Cc)) $ret .= "Cc: ".implode(",\r\n\t", $this->safeEncodeHeader($this->Cc))."\r\n";
			return $ret;
		}
	}
	/**
	 * Structure a given array of addresses into the 1-dim we want
	 * @param array unstructured
	 * @return array structured
	 * @private
	 */
	function parseAddressList($u_array)
	{
		$ret = array();
		foreach ($u_array as $val)
		{
			if (is_array($val)) $ret[] = '"'.$val[0].'" <'.$val[1].'>';
			else $ret[] = $val;
		}
		return $ret;
	}
	/**
	 * Send an email using Swift (send commands)
	 * @param  string  to_address
	 * @param  string  from_address
	 * @param  string  subject
	 * @param  string  body, optional
	 * @param  string  content-type,optional
	 * @param  string  content-transfer-encoding,optional
	 * @return  bool  successful
	 */
	function send($to, $from, $subject, $body=false, $type='text/plain', $encoding=false)
	{
		if ((strtoupper($this->charset) != 'UTF-8') && $body && $this->detectUTF8($body) && !$this->userCharset) $this->charset = 'UTF-8';
		if ((strtoupper($this->charset) != 'UTF-8') && $this->detectUTF8($subject) && !$this->userCharset) $this->charset = 'UTF-8';
		if ((strtoupper($this->charset) != 'UTF-8') && $this->detectUTF8($to) && !$this->userCharset) $this->charset = 'UTF-8';
		if ((strtoupper($this->charset) != 'UTF-8') && $this->detectUTF8($from) && !$this->userCharset) $this->charset = 'UTF-8';
		
		if (!$encoding && $this->_8bitmime) $encoding = '8bit';
		elseif (!$encoding) $encoding = 'quoted-printable';
		
		$to = (array) $to;
		$this->to = $this->parseAddressList($to);
		//In these cases we just send the one email
		if ($this->useExactCopy || !empty($this->Cc) || !empty($this->Bcc))
		{
			$this->currentMail = $this->buildMail(false, $from, $subject, $body, $type, $encoding, 1);
			$this->triggerEventHandler('onBeforeSend');
			foreach ($this->currentMail as $command)
			{
				//Number of successful addresses expected
				$this->numAddresses = 1;
				
				if (is_array($command))
				{ //Commands can be returned as 1-dimensional arrays
					$this->numAddresses = count($command);
					foreach ($command as $c)
					{
						if (!$this->command($c))
						{
							$this->logError('Sending failed on command: '.$c, 0);
							return false;
						}
					}
				}
				else if (!$this->command($command))
				{
					$this->logError('Sending failed on command: '.$command, 0);
					return false;
				}
			}
			$this->triggerEventHandler('onSend');
		}
		else
		{
			$get_body = true;
			$cached_body = '';
			foreach ($this->to as $address)
			{
				$this->currentMail = $this->buildMail($address, $from, $subject, $body, $type, $encoding, $get_body);
				//If we have a cached version
				if (!$get_body) $this->currentMail[] = $this->makeRecipientHeaders($address).$cached_body;
				$this->triggerEventHandler('onBeforeSend');
				foreach ($this->currentMail as $command)
				{
					//This means we're about to send the DATA part
					if ($get_body && ($this->responseCode == 354 || $this->responseCode == -1))
					{
						$cached_body = $command;
						$command = $this->makeRecipientHeaders($address).$command;
					}
					if (is_array($command))
					{
						foreach ($command as $c)
						{
							if (!$this->command($c))
							{
								$this->logError('Sending failed on command: '.$c, 0);
								return false;
							}
						}
					}
					else if (!$this->command($command))
					{
						$this->logError('Sending failed on command: '.$command, 0);
						return false;
					}
				}
				$this->triggerEventHandler('onSend');
				$get_body = false;
			}
		}
		if ($this->autoFlush) $this->flush(true); //Tidy up a bit
		return true;
	}
	/**
	 * Builds the list of commands to send the email
	 * The last command in the output is the email itself (DATA)
	 * The commands are as follows:
	 *  - MAIL FROM: <address> (0)
	 *  - RCPT TO: <address> (1)
	 *  - DATA (2)
	 *  - <email> (3)
	 *
	 * @param  string  to_address
	 * @param  string  from_address
	 * @param  string  subject
	 * @param  string  body, optional
	 * @param  string  content-type, optional
	 * @param  string  encoding, optional
	 * @return  array  commands
	 * @private
	 */
	function buildMail($to, $from, $subject, $body, $type='text/plain', $encoding='8bit', $return_data_part=true)
	{
		$date = date('r'); //RFC 2822 date
		$return_path = $this->returnPath ? $this->returnPath : $this->getAddress($from);
		$ret = array("MAIL FROM: ".$return_path."\r\n"); //Always
		//If the user specifies a different reply-to
		$reply_to = !empty($this->replyTo) ? $this->getAddress($this->replyTo) : $this->getAddress($from);
		//Standard headers
		$this->from = $from;
		$data = "From: ".$this->safeEncodeHeader($from)."\r\n".
			"Reply-To: ".$this->safeEncodeHeader($reply_to)."\r\n".
			"Subject: ".$this->safeEncodeHeader($subject)."\r\n".
			"Date: $date\r\n";
		if ($this->readReceipt) $data .= "Disposition-Notification-To: ".$this->safeEncodeHeader($from)."\r\n";
		
		if (!$to) //Only need one mail if no address was given
		{ //We'll collate the addresses from the class properties
			$data .= $this->getMimeBody($body, $type, $encoding)."\r\n.\r\n";
			$headers = $this->makeRecipientHeaders();
			//Rcpt can be run several times
			$rcpt = array();
			foreach ($this->to as $address) $rcpt[] = "RCPT TO: ".$this->getAddress($address)."\r\n";
			foreach ($this->Cc as $address) $rcpt[] = "RCPT TO: ".$this->getAddress($address)."\r\n";
			$ret[] = $rcpt;
			$ret[] = "DATA\r\n";
			$ret[] = $headers.$this->headers.$data;
			//Bcc recipients get to see their own Bcc header but nobody else's
			foreach ($this->Bcc as $address)
			{
				$ret[] = "MAIL FROM: ".$this->getAddress($from)."\r\n";
				$ret[] = "RCPT TO: ".$this->getAddress($address)."\r\n";
				$ret[] = "DATA\r\n";
				$ret[] = $headers."Bcc: ".$this->safeEncodeHeader($address)."\r\n".$this->headers.$data;
			}
		}
		else //Just make this individual email
		{
			if ($return_data_part) $mail_body = $this->getMimeBody($body, $type, $encoding);
			$ret[] = "RCPT TO: ".$this->getAddress($to)."\r\n";
			$ret[] = "DATA\r\n";
			if ($return_data_part) $ret[] = $data.$this->headers.$mail_body."\r\n.\r\n";
		}
		return $ret;
	}
	/**
	 * Returns the MIME-specific headers followed by the email
	 * content as a string.
	 * @param string body
	 * @param string content-type
	 * @param string encoding
	 * @return string mime data
	 * @private
	 */
	function getMimeBody($string, $type, $encoding)
	{
		if ($string) //Not using MIME parts
		{
			$body = $this->encode($string, $encoding);
			if ($this->autoCompliance) $body = $this->chunkSplitLines($body);
			$data = "Content-Type: $type; charset=\"{$this->charset}\"; format=flowed\r\n".
				"Content-Transfer-Encoding: $encoding\r\n\r\n".
				$this->makeSafe($body);
		}
		else
		{ //Build a full email from the parts we have
			$boundary = $this->getMimeBoundary();
			$encoding = '8bit';
			$mixalt = 'alternative';
			$alternative_boundary = $this->getMimeBoundary(implode($this->parts));

			if (!empty($this->images))
			{
				$mixalt = 'mixed';
				$related_boundary = $this->getMimeBoundary(implode($this->parts).implode($this->images));
				
				$message_body = "Content-Type: multipart/related; ".
					"boundary=\"{$related_boundary}\"\r\n\r\n".
					"--{$related_boundary}\r\n";
				
				$parts_body = "Content-Type: multipart/alternative; ".
					"boundary=\"{$alternative_boundary}\"\r\n\r\n".
					"--{$alternative_boundary}\r\n".
					implode("\r\n\r\n--$alternative_boundary\r\n", $this->parts).
					"\r\n--$alternative_boundary--\r\n";
				
				$message_body .= $parts_body.
					"--$related_boundary\r\n";
				
				$images_body = implode("\r\n\r\n--$related_boundary\r\n", $this->images);
				
				$message_body .= $images_body.
					"\r\n--$related_boundary--\r\n";
				
			}
			else
			{
				if (!empty($this->attachments))
				{
					$message_body = "Content-Type: multipart/alternative; ".
					"boundary=\"{$alternative_boundary}\"\r\n\r\n".
					"--{$alternative_boundary}\r\n".
					implode("\r\n\r\n--$alternative_boundary\r\n", $this->parts).
					"\r\n--$alternative_boundary--\r\n";
				}
				else $message_body = implode("\r\n\r\n--$boundary\r\n", $this->parts);
			}
	
			if (!empty($this->attachments)) //Make a sub-message that contains attachment data
			{
				$mixalt = 'mixed';
				$message_body .= "\r\n\r\n--$boundary\r\n".
					implode("\r\n--$boundary\r\n", $this->attachments);
			}
			
			$data = "MIME-Version: 1.0\r\n".
				"Content-Type: multipart/{$mixalt};\r\n".
				"	boundary=\"{$boundary}\"\r\n".
				"Content-Transfer-Encoding: {$encoding}\r\n\r\n".
				"{$this->mimeWarning}\r\n".
				"--$boundary\r\n".
				"$message_body\r\n".
				"--$boundary--";
		}
		return $data;
	}
}

?>
