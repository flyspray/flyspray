<?php
/**
 * Flyspray dokuwiki mention plugin
 *
 * @author peterdd
 */
 
if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__).'/../../').'/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_mention extends DokuWiki_Syntax_Plugin
{
	/**
	* return some info
	*/
	function getInfo()
	{
		return array(
			'author' => 'peterdd',
			'email'  => '',
			'date'   => '2019-05-05',
			'name'   => 'FS-mention Plugin',
			'desc'   => 'links mentioned users in task or comment to their profile page',
			'url'    => 'https://www.flyspray.org/',
		);
	}
 
	/**
	* What kind of syntax are we?
	*/
	function getType()
	{
		return 'substition';
	}
 
	/**
	* Where to sort in?
	*/
	function getSort()
	{
		# TODO findout which best fits with the other plugins
		return 302;
	}
 
	/**
	* Connect pattern to lexer
	*
	*/
	function connectTo($mode)
	{
		$this->Lexer->addSpecialPattern('@[a-zA-Z0-9_\.\-]+', $mode, 'plugin_mention');
	}
 
	/**
	* Handle the match
	*/
	function handle($match, $state, $pos, &$handler)
	{
		return array($match, $state);
	}            
 
	/**
	* Create output
	*/
	function render($mode, &$renderer, $data)
	{
		if ($mode == 'xhtml') {
			$renderer->doc .= tpl_mentionlink($data[0]);
		}
		return true;
	}
} // end class
