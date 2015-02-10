<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php 
# Currently only for development usage!
die("Comment me out before using, I'm in line " . __LINE__); 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lang diff</title>
<link type="text/css" rel="stylesheet" href="/themes/CleanFS/theme.css" media="screen">
<style type="text/css">
body{font-size:100%;}
pre { margin : 0; }
table{border-collapse:collapse;}
.progress_bar_container{height:20px;}
.progress_bar_container span{font-size:100%;}
.progress_bar_container span:first-child{display:inline-block;margin-top:2px;}
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
</head>
<body>
<?php
require_once dirname(dirname(__FILE__)) . '/includes/fix.inc.php';
  /*
  Usage: Open this file like .../.langdiff?lang=de in your browser.
         "de" represents your language code.
  */
    $lang = ( isset($_GET['lang']) ? $_GET['lang'] : 'en');
    if (!ctype_alnum($lang)) {
        die('Invalid language name.');
    }
    
    require_once('en.php');
    // while the en.php and $lang.php both defines $language, the english one should be keept 
    $orig_language = $language;



    $translationfile = "$lang.php";
    if ($lang != 'en' && file_exists($translationfile)) {
        include_once($translationfile);
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
	echo '<table cellspacing="0">';
	$i = 0;
        foreach ($orig_language as $key => $val) {
            if (!isset($translation[$key])) {
                echo '<tr class="line',($i%2),'"><th>',$key,'</th><td><pre>\'',$val,'\'</pre></td></tr>',"\n";
		$i++;
            }
	    
        }
	echo '</table>';
	if ( $i > 0 )
		echo '<p>',$i,' out of ',sizeof($language),' keys to translate.</p>';
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
	echo '<h2><a name="compare"></a>Direct comparision between english and '.htmlspecialchars($lang).'</h2>';
	echo '<table>
		<colgroup></colgroup>
		<thead><tr>
		<th><a href="?lang='.htmlspecialchars($lang).'&amp;sort=key&#compare" title="sort by translation key">translation key</th>
		<th><a href="?lang='.htmlspecialchars($lang).'&amp;sort=en&#compare" title="sort by english">en</a></th>
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
	# TODO show all existing translsations overview and selection
	#die('Translation does not exist.');
	# readdir
	require_once('en.php');
	$english=$language;
	$max=count($english);
	unset($language);
	if ($handle = opendir('.')) {
		$languages=array();
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file!='.langdiff.php' && $file!='.langedit.php' && !(substr($file,-4)=='.bak') && !(substr($file,-5)=='.safe') ) {
				$langfiles[]=$file;
			}
		}
		asort($langfiles);
		echo '<table class="overview"><thead><tr><th>File</th><th>Progress</th><th> </th></tr></thead>';
		foreach($langfiles as $lang){
			unset($translation);
			require_once($lang); # file $language variable
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
<td><a href="?lang='.substr($lang,0,-4).'">'.$lang.'</a></td>
<td><a href="?lang='.substr($lang,0,-4).'" class="progress_bar_container">
<span class="progress">'.$progress.' %</span>
<span style="width:'.$progress.'%" class="progress_bar"></span>
</span></td>
<td><a class="button" href=".langedit.php?lang='.substr($lang,0,-4).'">Translate '.substr($lang,0,-4).'</a></td>
</tr>';
			}else{
				echo '<tr><td>en.php</td><td>is reference and fallback</td><td><a class="button" href=".langedit.php?lang='.substr($lang,0,-4).'">Translate '.substr($lang,0,-4).'</a></td></tr>';
			}
		}
		closedir($handle);
		echo '</table>';
	}
    }
?>
</body>
</html>
