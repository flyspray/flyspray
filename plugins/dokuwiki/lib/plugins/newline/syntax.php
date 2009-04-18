<?php
/**
 * Adds simple <br />s
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Florian Schmitz floele at gmail dot com
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_newline extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Florian Schmitz',
            'email'  => 'floele@gmail.com',
            'date'   => '2005-12-17',
            'name'   => '<br /> Plugin',
            'desc'   => 'Enables simple newlines',
            'url'    => 'http://flyspray.org/',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 201;
    }

    /**
     * Connect pattern to lexer
     */

    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("(?<!^|\n)\n(?!\n)",$mode,'plugin_newline');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        return array($match, $state);
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
          if ($data[0]) $renderer->doc .= '<br />';
          return true;
        }
        return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>