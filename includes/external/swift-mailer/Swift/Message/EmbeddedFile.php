<?php

/**
 * Swift Mailer Embedded File (like an image or a midi file)
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_Message_Attachment");

/**
 * Embedded File component for Swift Mailer
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Message_EmbeddedFile extends Swift_Message_Attachment
{
	/**
	 * The content-id in the headers (used in <img src=...> values)
	 * @var string
	 */
	var $cid = null;
	
	/**
	 * Constructor
	 * @param mixed The input source.  Can be a file or a string
	 * @param string The filename to use, optional
	 * @param string The MIME type to use, optional
	 * @param string The Content-ID to use, optional
	 * @param string The encoding format to use, optional
	 */
	function Swift_Message_EmbeddedFile($data=null, $name=null, $type="application/octet-stream", $cid=null, $encoding="base64")
	{
		$this->Swift_Message_Attachment($data, $name, $type, $encoding, "inline");
		
		if ($cid === null)
		{
			Swift_ClassLoader::load("Swift_FileNameMaker");
			$maker =& Swift_FileNameMaker::instance();
			$cid = $maker->generate("swift-" . uniqid(time()) . ".");
		}
		$this->setContentId($cid);
		
		if ($name === null) $this->setFileName($cid);
		
		$this->headers->set("Content-Description", null);
		$this->headers->setAttribute("Content-Type", "name", null);
	}
	/**
	 * Set the Content-Id to use
	 * @param string The content-id
	 */
	function setContentId($id)
	{
		$id = (string) $id;
		$this->cid = $id;
		$this->headers->set("Content-ID", "<" . $id . ">");
	}
	/**
	 * Get the content-id of this file
	 * @return string
	 */
	function getContentId()
	{
		return $this->cid;
	}
}
