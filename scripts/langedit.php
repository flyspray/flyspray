
<?php
/* langedit.php
 *
 * Translation tool for the Flyspray Bug Tracker System
 * http://flyspray.org
 *
 * Author
 *  Lars-Erik Hoffsten
 *  larserik@softpoint.nu
 *
 * 2006-06-05 Version 1.0
 *  Initial version
 * 2006-06-05 Version 1.1
 *  Using UTF-8 character encoding
 *  Handles all kinds of characters like line feed etc that need special escaping
 *  Hides backup files with leading '.' in filename
 *  Creates a work file for better safety
 *  New languages are easily created just by typing the new language code on the URL
 * 2006-06-07 Version 1.2
 *  Moved to the setup directory so that it wouldn't be left behind in the
 *  installation to be used by some one unauthorized
 *  mb_strlen() replaced by strlen(utf_decode()) because mb_* functions are not standard
 * 2006-06-12 Version 1.3
 *  Writes correct array name for english
 *
 * 2015-02-09
 *  use flyspray theme, add button targeting translation overview for better workflow
 * 2015-03-02
 *  integration into flyspray user interface
 *
 * Usage: http://.../flyspray/?do=langedit&lang=sv
 * "sv" represents your language code.
 *
 * !!!
 * Note that this script rewrites the language file completely when saving.
 * Anything else than the $translation array will be lost including any file comments.
 * !!!
 */

if(!defined('IN_FS')) {
    die('Do not access this file directly.');
}

# let also project managers allow translation of flyspray
if(!$user->perms('manage_project')) {
    Flyspray::show_error(28);
}

// Make it possible to reload page after updating language
// Don't want to send form data again if user reloads the page
ob_start();
?>
<script language="javascript">
// Indicate which texts are changed, called from input and textarea onchange
function set(id){
  var checkbox = document.getElementById('id_checkbox_' + id);
  if(checkbox)
    checkbox.checked = true;
  var hidden = document.getElementById('id_hidden_' + id);
  if(hidden)
    hidden.disabled = false;
  var conf = document.getElementById('id_confirm');
  if(conf)
    conf.disabled = true;
}
</script>
<?php

// Set current directory to where the language files are
chdir('lang');

$lang = isset($_GET['lang']) ? $_GET['lang']:false;
$fail = '';
if(!$lang || !preg_match('/^[a-zA-Z0-9_]+$/', $lang)){
	$fail .= "Language code not supplied correctly<br>\n";
}
if(!file_exists('en.php')) {
	$fail .= "The english language file <code>en.php</code> is missing. Make sure this script is run from the same directory as the language files <code>.../flyspray/lang/</code><br>\n";
}
if($fail) {
	die($fail."<b>Usage:</b> <a href='?do=langedit&lang='>&lt;lang code&gt;</a> where &lt;lang code&gt; should be replaced by your language, e.g. <b>de</b> for German.");
}
// Read english language file in array $language (assumed to be UTF-8 encoded)
require 'en.php';
if(!is_array(@$language)){
	die("Invalid language file for english");
}
$count = count($language);

// Read the translation file in array $translation (assumed to be UTF-8 encoded)
$working_copy = false;
if(!file_exists($lang.'.php') && !file_exists('.'.$lang.'.php.work')) {
	echo '<h3>A new language file will be created: <code>'.$lang.'.php</code></h2>';
} else {
	if($lang != 'en') {
		if(file_exists('.'.$lang.'.php.work')) {
			$working_copy = true;
			include_once '.'.$lang.'.php.work'; // Read the translation array (work in progress)
		} else{
			include $lang.'.php'; // Read the original translation array - maybe again, no _once here!
		}
	} else if(file_exists('.en.php.work')){
		$working_copy = true;
		$tmp = $language;
		include_once '.en.php.work'; // Read the language array (work in progress)
		$translation = $language;  // Edit the english language file
		$language = $tmp;
	} else{
		$translation = $language;  // Edit the english language file
	}

	if(!is_array(@$translation)){
		echo "<b>Warning: </b>the translation file does not contain the \$translation array, a new file will be created: <code>$lang.php</code>\n";
	}
}

$limit = 30;
$begin = isset($_GET['begin']) ? (int)($_GET['begin'] / $limit) * $limit : 0;

// Was show missing pressed?
$show_empty = (!isset($_POST['search']) && isset($_REQUEST['empty']));  // Either POST or URL
// Any text in search box?
if(!$show_empty && isset($_POST['search_for'])) {
	$search = trim($_POST['search_for']);
} else if(!$show_empty && isset($_GET['search_for'])) {
	$search = trim(urldecode($_GET['search_for']));
} else {
	$search = "";
}
// Path to this file
$self = "{$_SERVER['SCRIPT_NAME']}?do=langedit&lang=$lang";

if(isset($_POST['confirm'])) {
  // Make a backup
  unlink(".$lang.php.bak");
  rename("$lang.php", ".$lang.php.bak");
  rename(".$lang.php.work", "$lang.php");
  // Reload page, so that form data won't get posted again on refresh
  header("location: $self&begin=$begin" . ($search? "&search_for=".urlencode($search): "") . ($show_empty? "&empty=": ""));
  exit;
} else if(isset($_POST['submit']) && isset($_POST['L'])) {
  // Save button was pressed
  update_language($lang, $_POST['L'], @$_POST['E']);
  // Reload page, so that form data won't get posted again on refresh
  header("location: $self&begin=$begin" . ($search? "&search_for=".urlencode($search): "") . ($show_empty? "&empty=": ""));
  exit;
}

// One form for all buttons and inputs
echo '<a class="button" href="?do=langdiff">Overview</a>';
echo "<form action=\"$self&do=langedit&begin=$begin". ($show_empty? "&empty=": "") . "\" method=\"post\">\n";
echo "<table cellspacing=0 cellpadding=1>\n<tr><td colspan=3>";
// Make page links
for($p = 0; $p < $count; $p += $limit){
	if($p){
		echo " | ";
	}
	$bgn = $p+1;
	$end = min($p+$limit, $count);
	if($p != $begin || $search || $show_empty) {
		echo "<a href=\"$self&begin=$bgn\">$bgn&hellip;$end</a>\n";  // Show all links when searching or display all missing strings
	} else {
		echo "<b>$bgn&hellip;$end</b>\n";
	}
}
?>
</td><td>
<input type="submit" name="submit" value="Save changes" title="Saves changes to a work file">
<input type="submit" name="confirm" id="id_confirm" value="Confirm all changes"<?php echo !$working_copy ? ' disabled="disabled"': ''; ?> title="Confirm all changes and replace the original language file">
<br>
<?php
if($working_copy) {
  echo "Your changes are stored in <code>.$lang.php.work</code> until you press 'Confirm all changes'<br>";
}
// Search
echo '<input type="text" name="search_for" value="'.Filters::noXSS($search).'"><input type="submit" name="search" value="Search">';
// List empty
if($lang != 'en') {
  echo '<input type="submit" name="empty" value="Show missing" title="Show all texts that have no translation">';
}
?>
</td></tr>
<tr><th colspan=2>Key</th><th>English</th><th>Translation:<?php echo $lang; ?></th></tr>
<?php
$i = 0;  // Counter to find offset
$j = 0;  // Counter for background shading
foreach ($language as $key => $val){
  $trans = @$translation[$key];
  if((!$search && !$show_empty && $i >= $begin) ||
   ($search && (stristr($key, $search) || stristr($val, $search) || stristr($trans, $search))) ||
   ($show_empty && !$trans)){
    $bg = ($j++ & 1)? '#fff': '#eed';
    // Key
    echo '<tr style="background-color:'.$bg.'" valign="top"><td align="right">'.($i+1).'</td><td><b>'.$key.'</b></td>';
    // English (underline leading and trailing spaces)
    $space = '<b style="color:#c00;" title="Remember to include a space in the translation!">_</b>';
    echo '<td>'. (preg_match("/^[ \t]/",$val)? $space: "") . nl2br(htmlentities($val)). (preg_match("/[ \t]$/",$val)? $space: "") ."</td>\n";
    echo '<td align="right"><nobr>';
    echo '<input type="checkbox" disabled="disabled" id="id_checkbox_'.$key.'">';
    echo '<input type="hidden" disabled="disabled" id="id_hidden_'.$key.'" name="E['.$key.']">';
    // Count lines in both english and translation
    $lines = 1 + max(preg_match_all("/\n/", $val, $matches), preg_match_all("/\n/", $trans, $matches));
    // Javascript call on some input events
    $onchange = 'onchange="set(\''.$key.'\');" onkeypress="set(\''.$key.'\');"';
    // \ is displayed as \\ in edit fields to allow \n as line feed
    $trans = str_replace("\\", "\\\\", $trans);
    if($lines > 1 || strlen(utf8_decode($val)) > 60 || strlen(utf8_decode($trans)) > 60){
      // Format long texts for <textarea>, remove spaces after each new line
      $trans = preg_replace("/\n[ \t]+|\\n/", "\n", htmlentities($trans, ENT_NOQUOTES, "UTF-8"));
      echo '<textarea cols=79 rows='.max(4,$lines).' name="L['.$key.']" '.$onchange.'>'.$trans.'</textarea>';
    } else{
      // Format short texts for <input type=text>
      $trans = str_replace(array("\n", "\""), array("\\n", "&quot;"), $trans);
      echo '<input class="edit" type="text" name="L['.$key.']" value="'.$trans.'" size=80 '.$onchange.'>';
    }
    echo "</nobr></td></tr>\n";

    if(--$limit == 0 && !$search && !$show_empty){
      break;
    }
  }
  $i++;
}
?>
</table>
<hr>
<table width="100%">
<tr>
<td>The language files are UTF-8 encoded, avoid manual editing if You are not sure that your editor supports UTF-8<br>
Syntax for <b>\</b> is <b>\\</b> and for line feed type <b>\\n</b> in single line edit fields</td>
<td style="text-align: right;"><i>langedit by <a href="mailto:larserik@softpoint.nu">larserik@softpoint.nu</a></i></td>
</tr>
</table>
<?php

$content = ob_get_contents();
ob_end_clean();

$page->uses('content');
$page->pushTpl('admin.translation.tpl');

// Parse string for \n and \\ to be replaced by <lf> and \
function parseNL($str) {
  $pos = 0;
  while(($pos = strpos($str, "\\", $pos)) !== false){
    switch(substr($str, $pos, 2)){
    case "\\n":
      $str = substr_replace($str, "\n", $pos, 2);
      break;
    case "\\\\":
      $str = substr_replace($str, "\\", $pos, 2);
      break;
    }
    $pos++;
  }
  return $str;
}

function update_language($lang, &$strings, $edit) {
  global $language, $translation;

  if(!is_array($edit)) {
    return;
  }
  // Form data contains UTF-8 encoded text
  foreach($edit as $key => $dummy){
    if(@$strings[$key]) {
      $translation[$key] = parseNL($strings[$key]);
    } else {
      unset($translation[$key]);
    }
  }
  // Make a backup just in case!
  if(!file_exists(".$lang.php.safe")){
    // Make one safe backup that will NOT be changed by this script
    copy("$lang.php", ".$lang.php.safe");
  }
  if(file_exists(".$lang.php.work")){
    // Then make ordinary backups
    copy(".$lang.php.work", ".$lang.php.bak");
  }
  // Write the translation array to file with UNIX style line endings
  $file = fopen(".$lang.php.work", "w");
  // Write the UTF-8 BOM, Byte Order Marker
  //fprintf($file, chr(0xef).chr(0xbb).chr(0xbf));
  // Header
  fprintf($file, "<?php\n//\n"
    ."// This file is auto generated with langedit.php\n"
    ."// Characters are UTF-8 encoded\n"
    ."// \n"
    ."// Be careful when editing this file manually, some text editors\n"
    ."// may convert text to UCS-2 or similar (16-bit) which is NOT\n"
    ."// readable by the PHP parser\n"
    ."// \n"
    ."// Furthermore, nothing else than the language array is saved\n"
    ."// when using the langedit.php editor!\n//\n");
  if($lang == 'en') {
    fprintf($file, "\$language = array(\n");
  } else {
    fprintf($file, "\$translation = array(\n");
  }

  // The following characters will be escaped in multiline strings
  // in the following order:
  // \    => \\
  // "    => \"
  // $    => \$
  // <lf> => \n
  // <cr> are removed if any
  $pattern = array("\\",   "\"",   "\$",   "\n",  "\r");
  $replace = array("\\\\", "\\\"", "\\\$", "\\n", "");
  // Write the array to the file, ordered as the english language file
  foreach($language as $key => $val){
    $trans = @$translation[$key];
    if(!$trans) {
      continue;
    }
    if(strstr($trans, "\n")) {  // Use double quotes for multiline
      fprintf($file, "%-26s=> \"%s\",\n", "'$key'", str_replace($pattern, $replace, $trans));
    } else {  // Use single quotes for single lines, only \ and ' needs escaping
      fprintf($file, "%-26s=> '%s',\n", "'$key'", str_replace(array("\\","'"), array("\\\\", "\\'"), $trans));
    }
  }
  fprintf($file, ");\n\n?".">\n");  // PHP end tag currupts some syntax color coders
  fclose($file);
}

?>
