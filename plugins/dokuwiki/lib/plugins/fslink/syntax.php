<?php
/**
 * FS#X and bug X plugin
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
class syntax_plugin_fslink extends DokuWiki_Syntax_Plugin {
 
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Florian Schmitz',
            'email'  => 'floele@gmail.com',
            'date'   => '2005-12-17',
            'name'   => 'FS-link Plugin',
            'desc'   => 'Enables Flyspray\'s bug links',
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
        return 301;
    }
 
    /**
     * Connect pattern to lexer
     */
     
    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern('FS#\d+',$mode,'plugin_fslink');
        $this->Lexer->addSpecialPattern('bug \d+',$mode,'plugin_fslink');
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
            $fsid = explode('#', $data[0]);
            if(count($fsid) < 2) {
                $fsid = explode(' ', $data[0]);
            }
            $renderer->doc .= tpl_tasklink($fsid[1], $data[0]);
        }
        return true;
    }
     
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>