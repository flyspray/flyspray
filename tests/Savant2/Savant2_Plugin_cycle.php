<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Cycles through a series of values.
* 
* $Id: Savant2_Plugin_cycle.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
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

class Savant2_Plugin_cycle extends Savant2_Plugin {
	
	/**
	* 
	* An associative array of predefined cycle value sets.
	* 
	* You can preset cycle values via Savant::loadPlugin().
	* 
	* $conf = array(
	*     'values' => array(
	*         'lightdark' => array('light', 'dark'),
	*         'threesome' => array('one', 'two', 'three')
	*     )
	* );
	* 
	* $Savant->loadPlugin('cycle', $conf);
	* 
	* ... and in your template you can call:
	* 
	* $this->plugin('cycle', 'lightdark', $iteration);
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $values = array();
	
	
	/**
	* 
	* Cycles through a series of values.
	* 
	* @access public
	* 
	* @param string|array $cycle If a string, the preset cycle value key to use
	* from $this->cycles; if an array, use the array as the cycle values.
	* 
	* @param int $iteration The iteration number for the cycle.
	* 
	* @param int $repeat The number of times to repeat each cycle value.
	* 
	* @return mixed The value of the cycle iteration.
	* 
	*/
	
	function plugin($cycle, $iteration, $repeat = 1)
	{
		// get the proper value set as an array
		if (is_string($cycle) && isset($this->values[$cycle])) {
			$values = (array) $this->values[$cycle];
		} else {
			$values = (array) $cycle;
		}
		
		// prevent divide-by-zero errors
		if ($repeat == 0) {
			$repeat = 1;
		}
		
		// return the perper value for iteration and repetition
		return $values[($iteration / $repeat) % count($values)];
	}
}
?>