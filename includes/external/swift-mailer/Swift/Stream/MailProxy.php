<?php

/**
 * This is the mail() proxy for the Swift_Stream in Swift Mailer.
 *
 * @package	Swift
 * @version	>= 2.0.0
 * @author	Chris Corbyn
 * @date	28th August 2006
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

$extList = "";

/**
 * Simply contains a set of responses for SMTP commands
 */
class SmtpMsgStub
{
	/**
	 * Build a new EHLO response string advertising the extensions set
	 * @param array extensions
	 */
	function setExtensions($array)
	{
		global $extList;
		
		if (!empty($array)) $ret = "250-php-native-mail.swift Hello localhost.localdomain [127.0.0.1]";
		else return $extList = "250 php-native-mail.swift Hello localhost.localdomain [127.0.0.1]";
		
		$num = count($array);
		for ($i = 0; $i < $num; $i++)
		{
			if ($i < $num-1) $ret .= "\r\n250-".$array[$i];
			else $ret .= "\r\n250 ".$array[$i];
		}
		$extList = $ret;
	}
	
	function greeting()
	{
		return "220 php-native-mail.swift ESMTP SwiftStream 0.0.0 ".date('r');
	}
	
	function badCommand($custom=false)
	{
		if (!$custom) return "500 Bad command";
		else return "500 $custom";
	}
	
	function EHLO()
	{
		global $extList;
		return $extList;
	}
	
	function OK()
	{
		return "250 OK";
	}
	
	function dataGoAhead()
	{
		return "354 Go Ahead";
	}
}

/**
 * The observer for the Stream class
 * Sends SMTP responses to SMTP commands
 */
class Swift_Stream_MailProxy
{
	/**
	 * An instance of the stream which it resides inside
	 * @var object stream
	 */
	var $stream;
	/**
	 * The ending needed to finish a command
	 * @var string ending
	 */
	var $ending = "\r\n";
	/**
	 * If a valid command has yet been sent
	 * @var bool valid command
	 */
	var $validCommandSent = false;
	/**
	 * If RCPT envelopes have been added
	 * @var bool rcpt
	 */
	var $rcptSent = false;
	/**
	 * If a mail from envelope is present
	 * @var bool mail from
	 */
	var $mailSent = false;
	/**
	 * If the data command has just been set
	 * @var bool sending data
	 */
	var $dataSent = false;
	/**
	 * The actual Return-Path address read from MAIL FROM
	 * @var string sender
	 */
	var $from = "";
	/**
	 * Consistent line endings to use everywhere.  In this case, php.net manual
	 * talks rubbish.  Linux uses `sendmail -t' which (as per linux style) needs LF
	 * whereas windows uses SMTP which, of course need CRLF.
	 * @var string line ending
	 */
	var $le = "\n";
	
	/**
	 * Constructor
	 * @param object parent stream object
	 */
	function Swift_Stream_MailProxy(&$stream)
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->le = "\r\n";
		
		$this->stream =& $stream;
		$this->stream->setResponse($this->getGreeting());
	}
	/**
	 * Try to get the SMTP greeting
	 * @return string response
	 */
	function getGreeting()
	{
		$this->validCommandSent = false;
		return SmtpMsgStub::greeting();
	}
	/**
	 * Send a 500 error
	 * @return string response
	 */
	function getBadCommand()
	{
		return SmtpMsgStub::badCommand();
	}
	/**
	 * Try to get the EHLO response
	 * @return string response
	 */
	function getEHLO()
	{
		if (!$this->validCommandSent)
		{
			$this->validCommandSent = true;
			return SmtpMsgStub::EHLO();
		}
		else return $this->getBadCommand();
	}
	/**
	 * Try to send a 250 response
	 * @return string response
	 */
	function getOK()
	{
		$this->validCommandSent = true;
		return SmtpMsgStub::OK();
	}
	/**
	 * Try to get a suitable response to the MAIL from command
	 * @return string response
	 */
	function getMAIL()
	{
		if ($this->validCommandSent && !$this->rcptSent && !$this->dataSent)
		{
			$dot_atom_re = '[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+(?:\.[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+)*';
			$implemented_domain_re = '[-0-9A-Z]+(?:\.[-0-9A-Z]+)*';
			$full_pattern = $dot_atom_re.'(?:@'.$implemented_domain_re.')?'; 
			
			if (preg_match("/^\s*mail\ +from:\s*(?:(<".$full_pattern.">)|(".$full_pattern.")|(<\ *>))\s*\r\n$/i",
			$this->stream->command))
			{
				$this->from = substr($this->stream->command, 12, -3);
				$this->mailSent = true;
				return $this->getOK();
			}
			else return $this->getBadCommand();
		}
		else return $this->getBadCommand();
	}
	/**
	 * Try to get a suitable response to the RCPT to command
	 * @return string response
	 */
	function getRCPT()
	{
		if ($this->validCommandSent && $this->mailSent && !$this->dataSent)
		{
			$dot_atom_re = '[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+(?:\.[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+)*';
			$implemented_domain_re = '[-0-9A-Z]+(?:\.[-0-9A-Z]+)*';
			$full_pattern = $dot_atom_re.'(?:@'.$implemented_domain_re.')?'; 
			
			if (preg_match("/^\s*rcpt\ +to:\s*(?:(<".$full_pattern.">)|(".$full_pattern.")|(<\ *>))\s*\r\n$/i",
			$this->stream->command))
			{
				$this->rcptSent = true;
				return $this->getOK();
			}
			else return $this->getBadCommand();
		}
		else return $this->getBadCommand();
	}
	/**
	 * Try to get a suitable response to the QUIT command
	 * @return string response
	 */
	function getQUIT()
	{
		$this->validCommandSent = false;
		$this->dataSent = false;
		$this->mailSent = false;
		$this->rcptSent = false;
		$this->to = array();
		$this->from = "";
		return SmtpMsgStub::OK();
	}
	/**
	 * Try to get a suitable response to the RSET command
	 * @return string response
	 */
	function getRSET()
	{
		$this->dataSent = false;
		$this->mailSent = false;
		$this->rcptSent = false;
		$this->to = array();
		$this->from = "";
		return SmtpMsgStub::OK();
	}
	/**
	 * Try to get a suitable response to the DATA command
	 * @return string response
	 */
	function getDataGoAhead()
	{
		if ($this->validCommandSent && $this->rcptSent && !$this->dataSent)
		{
			if (preg_match("/^\s*data\s*\r\n/i", $this->stream->command))
			{
				$this->validCommandSent = true;
				$this->dataSent = true;
				$this->mailSent = false;
				$this->rcptSent = false;
				$this->ending = "\r\n.\r\n";
				return SmtpMsgStub::dataGoAhead();
			}
			else return $this->getBadCommand();
		}
		else return $this->getBadCommand();
	}
	/**
	 * Try to get a suitable response after a message was sent
	 * @return string response
	 */
	function getMessageSent()
	{
		$this->ending = "\r\n";
		$this->dataSent = false;
		$this->to = array();
		$this->from = "";
		return SmtpMsgStub::OK();
	}
	/**
	 * Try to get a suitable response after a message was sent
	 * @return string response
	 */
	function getMessageRejected()
	{
		$this->ending = "\r\n";
		$this->dataSent = false;
		$this->to = array();
		$this->from = "";
		return SmtpMsgStub::badCommand("Sorry but PHP mail() rejected the command");
	}
	/**
	 * Runs appropriate commands and get responses
	 * @return string response
	 */
	function command(&$string)
	{
		$this->stream->hanging = true;
		if (substr($string, -(strlen($this->ending))) != $this->ending) return;
		
		$keyword = @strtolower(preg_replace('/^\s*([A-Z]+)\s*.*\r\n$/i', '$1', $string));
		switch ($keyword)
		{
			case 'ehlo':
			$this->stream->setResponse($this->getEHLO());
			break;
			case 'mail':
			$this->stream->setResponse($this->getMAIL());
			break;
			case 'rcpt':
			$this->stream->setResponse($this->getRCPT());
			break;
			case 'data':
			$this->stream->setResponse($this->getDataGoAhead());
			break;
			case 'quit':
			$this->stream->setResponse($this->getQUIT());
			break;
			case 'rset':
			$this->stream->setResponse($this->getRSET());
			break;
			default:
			if ($this->dataSent) //Ready to send mail contents
			{
				if ($this->sendNativeMail($string))
				{ //If sending reports all ok
					$this->stream->setResponse($this->getMessageSent());
				}
				else
				{ //mail() didn't send the message
					$this->stream->setResponse($this->getMessageRejected());
				}
			} //We don't have an action for this command
			else $this->stream->setResponse($this->getBadCommand());
			break;
		}
		
		$string = "";
		$this->stream->hanging = false;
	}
	/**
	 * Attempt to parse the email into something we can send with mail()
	 * @param string parseable message
	 * @return boolean
	 */
	function sendNativeMail($string)
	{
		$original_from = @ini_get('sendmail_from');
		@ini_set('sendmail_from', $this->from);
		$extra = "-oi -f ".$this->from;
		$sent = @mail(
		  implode(', ', array_map('trim', $this->getTo($string))),
		  $this->getSubject($string),
		  $this->getBody($string),
		  $this->getHeaders($string),
		  $extra);
		@ini_set('sendmail_from', $original_from);
		return $sent;
	}
	/**
	 * Get the body part of the message (remove CRLF.CRLF from end, remove headers)
	 * @param string full message
	 * @return string body
	 */
	function getBody($string)
	{
		return str_replace("\r\n", "{$this->le}", substr($string, strpos($string, "\r\n\r\n")+4, -5));
	}
	/**
	 * Get the headers from the full message
	 * Removes the "To: " and "Subject: " fields to avoid duplication
	 * @param string full message
	 * @param boolean remove subject
	 * @return string headers
	 */
	function getHeaders($string, $remove_subj=true, $remove_to=true)
	{
		$headers = substr($string, 0, strpos($string, "\r\n\r\n"));
		if ($remove_to) $headers = preg_replace("/^To:.*?\r\n/im", "", $headers);
		if ($remove_subj) $headers = preg_replace("/^Subject:\ ?((?:.*?)\r\n(?:[\ \t]+.*?\r\n)*)/im", "", $headers);
		return str_replace("\r\n", "{$this->le}", $headers);
	}
	/**
	 * Get the subject line from the message
	 * @param string message
	 * @return string subject
	 */
	function getSubject($string)
	{
		$headers = $this->getHeaders($string, false);
		if (preg_match("/^Subject:\ ?((?:.*?){$this->le}(?:[\ \t]+.*?{$this->le})*)/im", $headers, $matches))
		{
			return trim($matches[1]);
		}
		else return "";
	}
	/**
	 * Read the list of To: addresses from the headers
	 * @param string full mail
	 * @return array addresses
	 */
	function getTo($string)
	{
		$headers = $this->getHeaders($string, false, false);
		if (preg_match("/^To:\ ?((?:.*?){$this->le}(?:[\ \t]+.*?{$this->le})*)/im", $headers, $matches))
		{
			return preg_split('/\s*,\s*/', $matches[1]);
		}
		else return "";
	}
}

?>