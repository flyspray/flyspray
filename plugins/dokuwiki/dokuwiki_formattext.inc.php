<?php
class dokuwiki_TextFormatter
{    
    function render($text, $type = null, $id = null, $instructions = null)
    {
        global $conf, $baseurl, $db;
        
        // Unfortunately dokuwiki also uses $conf
        $fs_conf = $conf;
        $conf = array();

        // Dokuwiki generates some notices
        error_reporting(E_ALL ^ E_NOTICE);
        if (!$instructions) {
            include_once(BASEDIR . '/plugins/dokuwiki/inc/parser/parser.php');
        }
        require_once(BASEDIR . '/plugins/dokuwiki/inc/common.php');
        require_once(BASEDIR . '/plugins/dokuwiki/inc/parser/xhtml.php');
        

        // Create a renderer
        $Renderer = new Doku_Renderer_XHTML();

        if (!is_string($instructions) || strlen($instructions) < 1) {
            $modes = p_get_parsermodes();
            
            $Parser = new Doku_Parser();
            
            // Add the Handler
            $Parser->Handler = new Doku_Handler();
            
            // Add modes to parser
            foreach($modes as $mode){
                $Parser->addMode($mode['mode'], $mode['obj']);
            }
            $instructions = $Parser->parse($text);

            
            // Cache the parsed text
            if (!is_null($type) && !is_null($id)) {
                $fields = array('content'=> serialize($instructions), 'type'=> $type , 'topic'=> $id,
                                'last_updated'=> time());

                $keys = array('type','topic');
                //autoquote is always true on db class
                $db->Replace('{cache}', $fields, $keys);
            }
        } else {
            $instructions = unserialize($instructions);
        }

        $Renderer->smileys = getSmileys();
        $Renderer->entities = getEntities();
        $Renderer->acronyms = getAcronyms();
        $Renderer->interwiki = getInterwiki();

        $conf = $fs_conf;
        $conf['cachedir'] = FS_CACHE_DIR; // for dokuwiki
        $conf['fperm'] = 0600;
        $conf['dperm'] = 0700;
        
        // Loop through the instructions
        foreach ($instructions as $instruction) {
            // Execute the callback against the Renderer
            call_user_func_array(array(&$Renderer, $instruction[0]), $instruction[1]);
        }

        $return = $Renderer->doc;

        // Display the output
        if (Get::val('histring')) {
            $words = explode(' ', Get::val('histring'));
            foreach($words as $word) {
                $return = html_hilight($return, $word);
            }
        }
        
        return $return;
    }
    function textarea( $name, $rows, $cols, $attrs = null, $content = null) {
    	
    	$name = htmlspecialchars($name, ENT_QUOTES, 'utf-8');
        $rows = intval($rows);
        $cols = intval($cols);
        $return = '<div id="dokuwiki_toolbar">'
        		. dokuwiki_TextFormatter::getDokuWikiToolbar( $attrs['id'] )
        		. '</div>';
        
        $return .= "<textarea name=\"{$name}\" cols=\"$cols\" rows=\"$rows\" ";
        if (is_array($attrs)) {
            $return .= join_attrs($attrs);
        }
        $return .= '>';
        if (!is_null($content)) {
            $return .= htmlspecialchars($content, ENT_QUOTES, 'utf-8');
        }
        $return .= '</textarea>';
        return $return;
    }
    /**
	 * Displays a toolbar for formatting text in the DokuWiki Syntax
	 * Uses Javascript
	 *
	 * @param string $textareaId
	 */
	function getDokuWikiToolbar( $textareaId ) {
		global $conf, $baseurl;
	
		return '<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'**\', \'**\', \''.$textareaId.'\'); return false;">
		  		<img src="'.$baseurl.'plugins/dokuwiki/img/format-text-bold.png" align="bottom" alt="Bold" title="Bold" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'//\', \'//\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/format-text-italic.png" align="bottom" alt="Italicized" title="Italicized" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'__\', \'__\', \''.$textareaId.'\'); return false;">
			<img src="'.$baseurl.'plugins/dokuwiki/img/format-text-underline.png" align="bottom" alt="Underline" title="Underline" border="0" /></a>
			
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'&lt;del&gt;\', \'&lt;/del&gt;\', \''.$textareaId.'\'); return false;">
			<img src="'.$baseurl.'plugins/dokuwiki/img/format-text-strikethrough.png" align="bottom" alt="Strikethrough" title="Strikethrough" border="0" /></a>
			
			<img src="'.$baseurl.'plugins/dokuwiki/img/divider.gif" align="bottom" alt="|" style="margin: 0 3px 0 3px;" />
			
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'======\', \'======\', \''.$textareaId.'\'); return false;">
			<img title="Level 1 Headline" src="'.$baseurl.'plugins/dokuwiki/img/h1.gif" align="bottom" width="23" height="22" alt="Heading1" border="0" /></a>

			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'=====\', \'=====\', \''.$textareaId.'\'); return false;">
			<img title="Level 2 Headline" src="'.$baseurl.'plugins/dokuwiki/img/h2.gif" align="bottom" width="23" height="22" alt="Heading2" border="0" /></a>

			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'====\', \'====\', \''.$textareaId.'\'); return false;">
			<img title="Level 3 Headline" src="'.$baseurl.'plugins/dokuwiki/img/h3.gif" align="bottom" width="23" height="22" alt="Heading3" border="0" /></a>
			
			<img title="Divider" src="'.$baseurl.'plugins/dokuwiki/img/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />
			
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'&#123;&#123;http://\', \'&#125;&#125;\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/image-x-generic.png" align="bottom" alt="Insert Image" title="Insert Image" border="0" /></a>
			
			<a tabindex="-1" href="javascript:void(0);" onclick="replaceText(\'\n  * \', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/ul.gif" align="bottom" width="23" height="22" alt="Insert List" title="Insert List" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="replaceText(\'\n  - \', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/ol.gif" align="bottom" width="23" height="22" alt="Insert List" title="Insert List" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="replaceText(\'----\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/hr.gif" align="bottom" width="23" height="22" alt="Horizontal Rule" title="Horizontal Rule" border="0" /></a>
				
			<img src="'.$baseurl.'plugins/dokuwiki/img/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />
			
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'[[http://example.com|External Link\', \']]\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/text-html.png" align="bottom" alt="Insert Hyperlink" title="Insert Hyperlink" border="0" /></a>					
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'[[\', \']]\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/email.png" align="bottom" alt="Insert Email" title="Insert Email" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'[[ftp://\', \']]\', \''.$textareaId.'\'); return false;">
				<img src="'.$baseurl.'plugins/dokuwiki/img/network.png" align="bottom" alt="Insert FTP Link" title="Insert FTP Link" border="0" /></a>
				
			<img src="'.$baseurl.'plugins/dokuwiki/img/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />
			
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'<code>\', \'</code>\', \''.$textareaId.'\'); return false;">
			<img src="'.$baseurl.'plugins/dokuwiki/img/source.png" align="bottom" alt="Insert Code" title="Insert Code" border="0" /></a>
			<a tabindex="-1" href="javascript:void(0);" onclick="surroundText(\'<code php>\', \'</code>\', \''.$textareaId.'\'); return false;">
			<img src="'.$baseurl.'plugins/dokuwiki/img/source_php.png" align="bottom" alt="Insert Code" title="Insert PHP Code" border="0" /></a>
		';
	}
}
?>