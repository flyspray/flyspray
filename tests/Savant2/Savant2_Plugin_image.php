<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs an <image ... /> tag.
*
* Support for alpha transparency of PNG files in Microsoft IE added by
* Edward Ritter; thanks, Edward.
* 
* $Id: Savant2_Plugin_image.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @author Edward Ritter <esritter@gmail.com>
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as
* published by the Free Software Foundation; either version 2.1 of the
* License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
* 
*/

class Savant2_Plugin_image extends Savant2_Plugin {
	
	
	/**
	* 
	* The document root.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $documentRoot = null;
	
	
	/**
	* 
	* The base directory for images within the document root.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $imageDir = null;
	
	
	/**
	* 
	* Outputs an <img ... /> tag.
	* 
	* Microsoft IE alpha PNG support added by Edward Ritter.
	* 
	* @access public
	* 
	* @param string $file The path to the image on the local file system
	* relative to $this->imageDir.
	* 
	* @param string $alt Alternative descriptive text for the image;
	* defaults to the filename of the image.
	* 
	* @param int $border The border width for the image; defaults to zero.
	* 
	* @param int $width The displayed image width in pixels; defaults to
	* the width of the image.
	* 
	* @param int $height The displayed image height in pixels; defaults to
	* the height of the image.
	* 
	*/
	
	function plugin($file, $alt = null, $height = null, $width = null,
		$attr = null)
	{
		// is the document root set?
		if (is_null($this->documentRoot)) {
			// no, so set it
			$this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
		}
		
		// the image file type code (PNG = 3)
		$type = null;
		
		// get the file information
		$info = false;
		if (strpos($file, '://') === false) {
			// no "://" in the file, so it's local
			$file = $this->imageDir . $file;
			$info = @getimagesize($this->documentRoot . $file);
		} else {
			// get the file size info as from a stream
			$info = @getimagesize($file);
		}
		
		// did we find the file?
		if (is_array($info)) {
		
			// capture type info regardless
			$type = $info[2];
			
			// capture size info where both not specified
			if (is_null($width) && is_null($height)) {
				$width = $info[0];
				$height = $info[1];
			}
		}
		
		// clean up
		unset($info);
		
		// is the file a PNG? if so, check user agent, we will need to
		// make special allowances for Microsoft IE.
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') && $type === 3) {
			
			// support alpha transparency for PNG files in MSIE
			$html = '<span style="position: relative;';
			
			if ($height) {
				$html .= ' height: ' . $height . 'px;';
			}
			
			if ($width) {
				$html .= ' width: ' . $width . 'px;';
			}
			
			$html .= ' filter:progid:DXImageTransform.Microsoft.AlphaImageLoader';
			$html .= "(src='$file',sizingMethod='scale');\"";
			$html .= ' title="' . htmlspecialchars($alt) . '"';
			
			$html .= $this->_attr($attr);

			// done
			$html .= '></span>';
			
		} else {
			
			// not IE, so build a normal image tag.
			$html = '<img';
			$html .= ' src="' . htmlspecialchars($file) . '"';
			
			// add the alt attribute
			if (is_null($alt)) {
				$alt = basename($file);
			}
			$html .= ' alt="' . htmlspecialchars($alt) . '"';
			
			// add the height attribute
			if ($height) {
				$html .= ' height="' . htmlspecialchars($height) . '"';
			}
			
			// add the width attribute
			if ($width) {
				$html .= ' width="' . htmlspecialchars($width) . '"';
			}
			
			$html .= $this->_attr($attr);
			
			// done
			$html .= ' />';
			
		}
		
		// done!
		return $html;
	}
	
	
	/**
	* 
	* Create additional HTML attributes.
	* 
	* @access private
	* 
	* @param array|string $attr An array or string of attributes.
	* 
	* @return string A string of attributes.
	* 
	*/
	
	function _attr($attr = null)
	{
		$html = '';
		
		// add other attributes
		if (is_array($attr)) {
			// from array
			foreach ($attr as $key => $val) {
				$key = htmlspecialchars($key);
				$val = htmlspecialchars($val);
				$html .= " $key=\"$val\"";
			}
		} elseif (! is_null($attr)) {
			// from scalar
			$html .= " $attr";
		}
		
		return $html;
	}
}

?>