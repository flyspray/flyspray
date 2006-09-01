<?php
require_once 'Savant2/Plugin.php';

/**
* 
* The parent of all plugins, provides methods used by all plugins.
* 
* @author Anders Betnér 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*
**/

class FlyspraySavant2Plugin extends Savant2_Plugin {
   /**
   * 
   * Creates a three column html table row
   * 
   * @access public
   * 
   * @param string $col1 Content col1
   * @param string $col2 Content col2
   * @param string $col3 Content col3 (can be left out)
   * 
   * @return string html row definition
   */
   function _createTR($col1, $col2, $col3 = '') {
      return "  <tr>\n    <td>$col1</td>\n    <td>$col2</td>\n    <td>$col3</td>\n  </tr>\n";
   }
}
?>



