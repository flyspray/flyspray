<?php

if (!defined('IN_FS')) {
	die('Do not access this file directly.');
}

# let also project managers allow translation of Flyspray
if(!$user->perms('manage_project')) {
	Flyspray::show_error(28);
}

ob_start();

?>
<div id="toolbox" class="toolbox_translations">
<h3><?php echo eL('translations'); ?></h3>
<style type="text/css">
	pre {
		margin: 0;
	}
	table.overview {
		border-collapse: collapse;
		width: 100%;
		max-width: 400px;
	}
	table.overview .progress_bar_container {
		height: 20px;
	}
	table.overview .progress_bar_container span:first-child {
		display: inline-block;
		margin-top: 2px;
		z-index:101;
		color:#000;
	}
	.overview td, .overview th {
		border: none;
		padding: 0;
	}
	.overview a.button {
		padding: 2px 10px 2px 10px;
		margin: 2px;
	}
	table.overview th {
		text-align: center;
	}

	table.overview th, table.overview td {
		vertical-align: middle;
	}
	tr:hover td, tr:hover th {
		background : #e0e0e0;
	}

</style>
<?php
require_once BASEDIR . '/includes/fix.inc.php';
/*
 * Usage: Open this file like ?do=langdiff?lang=de in your browser.
 *    "de" represents your language code.
 */
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
if (preg_match('/[^a-zA-Z_]/', $lang)) {
	die('Invalid language name.');
}

# reload en.php if flyspray did it before!
require BASEDIR . '/lang/en.php';
// while the en.php and $lang.php both define $language, the english one should be kept
$orig_language = $language;

$translationfile = '/lang/' . "$lang.php";
if ($lang != 'en' && file_exists(BASEDIR . $translationfile)) {
	# reload that file if flyspray did it before!
        include BASEDIR . $translationfile;
	if (isset($_GET['sort']) && $_GET['sort'] == 'key') {
		ksort($orig_language);
	} elseif (isset($_GET['sort']) && $_GET['sort'] == 'en') {
		asort($orig_language);
	} elseif (isset($_GET['sort']) && $_GET['sort'] == $_GET['lang']) {
		# todo
	} else {
		# show as it is in file en.php
	}

	echo '<h1>Diff report for language ',$lang,'</h1>',"\n";
	echo '<h2>The following translation keys are missing in the translation:</h2>';
	echo '<table>';
	$i = 0;
	foreach ($orig_language as $key => $val) {
		if (!isset($translation[$key])) {
			echo '<tr><th>',$key,'</th><td>' . htmlspecialchars($val) . '</td></tr>',"\n";
			$i++;
		}
	}
	echo '</table>';
	if ($i > 0) {
		echo '<p>',$i,' out of ',sizeof($language),' keys to translate.</p>';
	}
	echo '<h2>The following translation keys should be deleted from the translation:</h2>';
	echo '<table cellspacing="0">';
	$i = 0;
	foreach ($translation as $key => $val) {
		if (!isset($orig_language[$key])) {
			echo '<tr class="line',($i % 2),'"><th>',$key,'</th><td><pre>\'',$val,'\'</pre></td></tr>',"\n";
			$i++;
		}
	}
	echo '</table>';
	if ($i > 0) {
		echo '<p>'.$i.' entries can be removed from this translation.</p>';
	} else {
		echo '<p><i class="fa fa-check fa-2x"></i> None</p>';
	}
	echo '<h2><a name="compare"></a>Direct comparison between English and ' . htmlspecialchars(strtoupper($lang)) . '</h2>';
	echo '<table>
		<thead><tr>
		<th><a href="?do=langdiff&lang=' . htmlspecialchars($lang) . '&amp;sort=key#compare" title="sort by translation key">translation key</th>
		<th><a href="?do=langdiff&lang=' . htmlspecialchars($lang) . '&amp;sort=en#compare" title="sort by english">en</a></th>
		<th>' . htmlspecialchars($lang) . '</th>
		</tr>
		</thead>
		<tbody>';
	$i = 0;
	foreach ($orig_language as $key => $val) {
		if (!isset($translation[$key])) {
			echo '<tr><th>',$key,'</th><td>' . htmlspecialchars($val) . '</td><td></td></tr>' . "\n";
		} else {
			echo '
				<tr>
				<th>',$key,'</th><td>' . htmlspecialchars($val) . '</td>
				<td>' . htmlspecialchars($translation[$key]) . '</td>
				</tr>' . "\n";
		}
		$i++;
	}
	echo '</tbody></table>';
} else {
	# TODO show all existing translations overview and selection
	# readdir
	$english = $language;
	$max = count($english);
	$langfiles = array();
	$workfiles = array();
	if ($handle = opendir(BASEDIR . '/lang')) {
		$languages = array();
		while (false !== ($file = readdir($handle))) {
			if ($file != "."
			&& $file != ".."
			&& $file != '.langdiff.php'
			&& $file != '.langedit.php'
			&& !(substr($file, -4) == '.bak')
			&& !(substr($file, -5) == '.safe') ) {
				# if a .$lang.php.work file but no $lang.php exists yet
				if (substr($file, -5) == '.work') {
					if(!is_file(BASEDIR . '/lang/' . substr($file, 1, -5))) {
						$workfiles[] = $file;
					}
				} else {
					$langfiles[] = $file;
				}
			}
		}
		asort($langfiles);
		asort($workfiles);
		echo '<table class="overview">
		<thead><tr><th>' . L('file') . '</th><th>' . L('progress') . '</th><th> </th></tr></thead>
		<tbody>';
		foreach($langfiles as $lang) {
			unset($translation);
			require BASEDIR . '/lang/' . $lang; # file $language variable
			$i = 0;
			$empty = 0;
			foreach ($orig_language as $key => $val) {
				if (!isset($translation[$key])) {
					$i++;
				} else {
					if ($val == '') {
						$empty++;
					}
				}
			}
			$progress = floor(($max - $i) * 100 / $max * 10) / 10;
			if($lang != 'en.php') {
				echo '
					<tr>
					<td><a href="?do=langdiff&lang='.substr($lang,0,-4).'">'.$lang.'</a></td>
					<td><div class="progress_bar_container">
					<span class="progress">'.$progress.' %</span>
					<span style="width:'.$progress.'%" class="progress_bar"></span></div>
					</td>
					<td><a class="button" href="?do=langedit&lang='.substr($lang,0,-4).'">'.L('translate').' '.substr($lang,0,-4).'</a></td>
					</tr>';
			} else {
				echo '<tr><td>en.php</td><td>is reference and fallback</td><td><a class="button" href="?do=langedit&lang=' . substr($lang, 0, -4) . '">Edit ' . substr($lang, 0, -4).'</a></td></tr>';
			}
		}
		foreach($workfiles as $workfile) {
			echo '<tr>
				<td><a href="?do=langdiff&lang=' . substr($workfile, 1, -9) . '">' . $workfile . '</a></td>
				<td></td>
				<td><a class="button" href="?do=langedit&lang=' . substr($workfile, 1, -9) . '">' . L('translate') . ' ' . substr($workfile, 1, -9) . '</a></td>
				</tr>';
		}
		closedir($handle);
		echo '</tbody></table>';
	}
}
echo "</div>\n";
$content = ob_get_contents();
ob_end_clean();

$page->uses('content');

if ($do == 'langdiff') {
	$page->pushTpl('admin.translation.tpl');
}
