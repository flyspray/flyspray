<?php

if(!defined('IN_FS')) {
	die('Do not access this file directly.');
}

# let also project managers allow translation of flyspray
if(!$user->perms('manage_project')) {
	Flyspray::show_error(28);
}

ob_start();

?>
<style type="text/css">
pre { margin : 0; }
table{border-collapse:collapse;}
.progress_bar_container{height:20px;}
.progress_bar_container span:first-child{display:inline-block;margin-top:2px;z-index:101;color:#000;}
.overview{margin-left:auto;margin-right:auto;}
.overview td, .overview th{border:none;padding:0;}
a.button{padding:2px 10px 2px 10px;margin:2px;}
table th{text-align:center;}
table th, table td {
	vertical-align:middle;
	border: 1px solid #ccc;
	padding: 2px;
}
tr:hover td, tr:hover th { background : #e0e0e0; }
</style>
<?php
require_once dirname(dirname(__FILE__)) . '/includes/fix.inc.php';
/*
* Usage: Open this file like ?do=langdiff?lang=de in your browser.
*    "de" represents your language code.
*/
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
if( preg_match('/[^a-zA-Z_]/', $lang)) {
	die('Invalid language name.');
}

# reload en.php if flyspray did it before!
require('lang/en.php');
// while the en.php and $lang.php both defines $language, the english one should be keept
$orig_language = $language;

$translationfile = 'lang/'."$lang.php";
if ($lang != 'en' && file_exists($translationfile)) {
	# reload that file if flyspray did it before!
        include($translationfile);
	if( isset($_GET['sort']) && $_GET['sort']=='key'){
		ksort($orig_language);
	}elseif( isset($_GET['sort']) && $_GET['sort']=='en'){
		asort($orig_language);
	}elseif( isset($_GET['sort']) && $_GET['sort']==$_GET['lang']){
		# todo
	}else{
		# show as it is in file en.php
	}

        echo '<h1>Diff report for language ',$lang,'</h1>',"\n";
        echo '<h2>The following translation keys are missing in the translation:</h2>';
	echo '<table>';
	$i = 0;
        foreach ($orig_language as $key => $val) {
            if (!isset($translation[$key])) {
                echo '<tr><th>',$key,'</th><td>'.htmlspecialchars($val).'</td></tr>',"\n";
		$i++;
            }

        }
	echo '</table>';
	if ( $i > 0 ){
		echo '<p>',$i,' out of ',sizeof($language),' keys to translate.</p>';
	}
	echo '<h2>The following translation keys should be deleted from the translation:</h2>';
	echo '<table cellspacing="0">';
	$i = 0;
	foreach ($translation as $key => $val) {
		if ( !isset($orig_language[$key])) {
			  echo '<tr class="line',($i%2),'"><th>',$key,'</th><td><pre>\'',$val,'\'</pre></td></tr>',"\n";
			  $i++;
		}
	}
	echo '</table>';
	if ( $i > 0 ){
		echo '<p>'.$i.' entries can be removed from this translation.</p>';
	} else{
		echo '<p><i class="fa fa-check fa-2x"></i> None</p>';
	}
	echo '<h2><a name="compare"></a>Direct comparision between english and '.htmlspecialchars($lang).'</h2>';
	echo '<table>
		<colgroup></colgroup>
		<thead><tr>
		<th><a href="?do=langdiff&lang='.htmlspecialchars($lang).'&amp;sort=key#compare" title="sort by translation key">translation key</th>
		<th><a href="?do=langdiff&lang='.htmlspecialchars($lang).'&amp;sort=en#compare" title="sort by english">en</a></th>
		<th>'.htmlspecialchars($lang).'</th>
		</tr>
		</thead>
		<tbody>';
	$i = 0;
        foreach ($orig_language as $key => $val) {
            if (!isset($translation[$key])) {
              echo '<tr><th>',$key,'</th><td>'.htmlspecialchars($val).'</td><td></td></tr>'."\n";
            }else{
              echo '
	<tr>
	<th>',$key,'</th><td>'.htmlspecialchars($val).'</td>
	<td>'.htmlspecialchars($translation[$key]).'</td>
	</tr>'."\n";
            }
            $i++;
        }
	echo '</tbody></table>';
} else {
	# TODO show all existing translations overview and selection
	# readdir
	$english=$language;
	$max=count($english);
	$langfiles=array();
	$workfiles=array();
	if ($handle = opendir('lang')) {
		$languages=array();
		while (false !== ($file = readdir($handle))) {
			if ($file != "." 
			 && $file != ".." 
			 && $file!='.langdiff.php' 
			 && $file!='.langedit.php' 
			 && !(substr($file,-4)=='.bak') 
			 && !(substr($file,-5)=='.safe') ) {
				# if a .$lang.php.work file but no $lang.php exists yet
				if( substr($file,-5)=='.work'){ 
					if(!is_file('lang/'.substr($file,1,-5)) ){
						$workfiles[]=$file;
					}
				} else{ 
					$langfiles[]=$file;
				}
			}
		}
		asort($langfiles);
		asort($workfiles);
		echo '<table class="overview">
		<thead><tr><th>'.L('file').'</th><th>'.L('progress').'</th><th> </th></tr></thead>
		<tbody>';
		foreach($langfiles as $lang){
			unset($translation);
			require('lang/'.$lang); # file $language variable
			$i=0; $empty=0;
			foreach ($orig_language as $key => $val) {
				if (!isset($translation[$key])) {
					$i++;
				}else{
					if($val==''){
						$empty++;
					}
				}
			}
			$progress=floor(($max-$i)*100/$max*10)/10;
			if($lang!='en.php'){
				echo '
<tr>
<td><a href="?do=langdiff&lang='.substr($lang,0,-4).'">'.$lang.'</a></td>
<td><a href="?do=langdiff&lang='.substr($lang,0,-4).'" class="progress_bar_container">
<span class="progress">'.$progress.' %</span>
<span style="width:'.$progress.'%" class="progress_bar"></span></a>
</td>
<td><a class="button" href="?do=langedit&lang='.substr($lang,0,-4).'">'.L('translate').' '.substr($lang,0,-4).'</a></td>
</tr>';
			}else{
				echo '<tr><td>en.php</td><td>is reference and fallback</td><td><a class="button" href="?do=langedit&lang='.substr($lang,0,-4).'">Translate '.substr($lang,0,-4).'</a></td></tr>';
			}
		}
		foreach($workfiles as $workfile){
			echo '<tr>
			<td><a href="?do=langdiff&lang='.substr($workfile,1,-9).'">'.$workfile.'</a></td>
			<td></td>
			<td><a class="button" href="?do=langedit&lang='.substr($workfile,1,-9).'">'.L('translate').' '.substr($workfile,1,-9).'</a></td>
			</tr>';
		}
		closedir($handle);
		echo '</tbody></table>';
	}
}

$content = ob_get_contents();
ob_end_clean();

$page->uses('content');
$page->pushTpl('admin.translation.tpl');

?>
