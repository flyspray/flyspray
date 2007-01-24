<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php die("remove me before using, Im in line " . __LINE__); ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Lang diff</title>
	<style type="text/css">
table { border-collapse : collapse; }
pre { margin : 0; }
th, td {
	vertical-align: top;
	text-align : left;
	border : 1px solid #ccc;
	padding : 2px;
}
.line0 { background : #f9f9f9; }
.line1 { background : #f0f0f0; }
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
    
    $translation = "$lang.php";
    if ($lang != 'en' && file_exists($translation)) {
        include_once($translation);
        echo '<h1>Diff report for language ',$lang,'</h1>',"\n";
        echo '<h2>The following translations (keys) are missing in the translation:</h2>';
	echo '<table cellspacing="0">';
	$i = 0;
        foreach ($language as $key => $val) {
            if (!isset($translation[$key])) {
                echo '<tr class="line',($i%2),'"><th>',$key,'</th><td><pre>\'',$val,'\'</pre></td></tr>',"\n";
		$i++;
            }
	    
        }
	echo '</table>';
	if ( $i > 0 )
		echo '<p>',$i,' out of ',sizeof($language),' keys to translate.</p>';
	echo '<h2>The following translations (keys) should be deleted in the translation:</h2>';
	echo '<table cellspacing="0">';
	$i = 0;
	foreach ($translation as $key => $val) {
		if ( !isset($language[$key])) {
			  echo '<tr class="line',($i%2),'"><th>',$key,'</th><td><pre>\'',$val,'\'</pre></td></tr>',"\n";
			  $i++;
		}
	}
	echo '</table>';
    } else {
        die('Translation does not exist.');
    }
?>
</pre>
</body>
</html>
