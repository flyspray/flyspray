<?php

/**
 * Template plugin for doing basic string replacements on emails in a batch.
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

class Swift_Plugin_Template
{
	var $pluginName = 'Template';
	var $templateVars = array();
	var $swiftInstance;
	var $template = '';
	var $count = 0;
	
	//2-dimensional
	// First level MUST be numerically indexed starting at zero
	// Second level contains the replacements
	function Swift_Plugin_Template($template_vars=array())
	{
		$this->templateVars = $template_vars;
	}
	
	function loadBaseObject(&$object)
	{
		$this->swiftInstance =& $object;
	}
	
	//Split the headers from the mail body
	function getTemplate()
	{
		return substr($this->swiftInstance->currentMail[3], strpos($this->swiftInstance->currentMail[3], "\r\n\r\n"));
	}
	
	function getHeaders()
	{
		return substr($this->swiftInstance->currentMail[3], 0, strpos($this->swiftInstance->currentMail[3], "\r\n\r\n"));
	}
	
	function onBeforeSend()
	{
		if (empty($this->template)) $this->template = $this->getTemplate();
		
		foreach ($this->templateVars[$this->count] as $key => $replacement)
		{
			$this->swiftInstance->currentMail[3] = $this->getHeaders().str_replace('{'.$key.'}', $replacement, $this->template);
		}
		$this->count++;
	}
}

?>