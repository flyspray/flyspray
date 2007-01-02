<?php
/**
 * Change-Interwikilinks Plugin
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
class syntax_plugin_changelinks extends DokuWiki_Syntax_Plugin {
 
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Florian Schmitz',
            'email'  => 'floele@gmail.com',
            'date'   => '2005-12-18',
            'name'   => 'Change-Interwikilinks Plugin',
            'desc'   => 'Changes the functionality of interwikilinks',
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
        return 299;
    }
 
    /**
     * Connect pattern to lexer
     */
     
    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[.+?\]\]",$mode,'plugin_changelinks');
    }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/','/\]\]$/u'),'',$match);
        
        // Split title from URL
        $link = preg_split('/\|/u',$link,2);
        if ( !isset($link[1]) ) {
            $link[1] = NULL;
        } else if ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
            // If the title is an image, convert it to an array containing the image details
            $link[1] = Doku_Handler_Parse_Media($link[1]);
        }
        $link[0] = trim($link[0]);

        //decide which kind of link it is

        if ( preg_match('/^[a-zA-Z]+>{1}.*$/u',$link[0]) ) {
        // Interwiki
            $interwiki = preg_split('/>/u',$link[0]);
            $handler->_addCall(
                'interwikilink',
                array($link[0],$link[1],strtolower($interwiki[0]),$interwiki[1]),
                $pos
                );
        } elseif ( preg_match('/^\\\\\\\\[\w.:?\-;,]+?\\\\/u',$link[0]) ) {
        // Windows Share
            $handler->_addCall(
                'windowssharelink',
                array($link[0],$link[1]),
                $pos
                );
        } elseif ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$link[0]) ) {
        // external link (accepts all protocols)
            $handler->_addCall(
                    'externallink',
                    array($link[0],$link[1]),
                    $pos
                    );
        } elseif ( preg_match('#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i',$link[0]) ) {
        // E-Mail
            $handler->_addCall(
                'emaillink',
                array($link[0],$link[1]),
                $pos
                );
        } elseif ( preg_match('!^#.+!',$link[0]) ){
        // local link
            $handler->_addCall(
                'locallink',
                array(substr($link[0],1),$link[1]),
                $pos
                );
        } else {
            return array($link[0],$link[1]);
        }
    }            
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml') {
            global $conf;
            $id = $data[0];
            $name = $data[1];
           
            //prepare for formating
            $link['target'] = $conf['target']['wiki'];
            $link['style']  = '';
            $link['pre']    = '';
            $link['suf']    = '';
            $link['more']   = '';
            $link['class']  = 'internallink';
            $link['url']    = DOKU_INTERNAL_LINK . $id;
            $link['name']   = ($name) ? $name : $id;
            $link['title']  = ($name) ? $name : $id;
            //add search string
            if($search){
                ($conf['userewrite']) ? $link['url'].='?s=' : $link['url'].='&amp;s=';
                $link['url'] .= urlencode($search);
            }
    
            //output formatted
            $renderer->doc .= $renderer->_formatLink($link);
        }
        return true;
    }
     
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>