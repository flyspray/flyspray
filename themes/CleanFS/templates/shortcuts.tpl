<?php
# TODO: show only or at least highlight the section that is matching the current view.

# following decisions based on:
# https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/accesskey
# https://en.wikipedia.org/wiki/Access_key#Access_in_different_browsers
if (isset($_SESSION['ua'])) {
	if ($_SESSION['ua']['platform'] == 'MacOSX') {
		# all Browsers on Apple Macs
		$modifier='<kbd>Ctrl</kbd> + <kbd>⌥ Opt</kbd>';
	} elseif ($_SESSION['ua']['browser'] == 'Firefox') {
		# Firefox on Windows and Linux
		$modifier='<kbd>Alt</kbd> + <kbd>⇧ Shift</kbd>';
	} else {
		# all others
		$modifier='<kbd>Alt</kbd>';
	}	
} else {
	# fallback
	$modifier='<kbd>Alt</kbd> + <kbd>⇧ Shift</kbd>';
}
?>
<input type="checkbox" id="s_shortcuts" />
<label for="s_shortcuts" id="shortcutlabel"><i class="fa fa-keyboard-o"></i> <?= eL('keyboardshortcuts') ?></label>
<label for="s_shortcuts" id="shortcutsmodal"></label>
<div id="shortcuts">
<label for="s_shortcuts" id="shortcutclose"><i class="fa fa-close fa-2x"></i></label>
<h3><?= eL('availablekeybshortcuts') ?></h3>
<h4></h4>
<ul>
<li><?= $modifier ?> + <kbd>l</kbd> <?= eL('logindialoglogout') ?></li>
<li><?= $modifier ?> + <kbd>a</kbd> <?= eL('addnewtask') ?></li>
<li><?= $modifier ?> + <kbd>m</kbd> <?= eL('mysearch') ?></li>
<li><?= $modifier ?> + <kbd>t</kbd> <?= eL('focustaskidsearch') ?></li>
</ul>
<h4><?= eL('tasklist') ?></h4>
<ul>
<li><kbd>o</kbd> <?= eL('openselectedtask') ?></li>
<li><kbd>j</kbd> <?= eL('movecursordown') ?></li>
<li><kbd>k</kbd> <?= eL('movecursorup') ?></li>
</ul>
<h4><?= eL('taskdetails') ?></h4>
<ul>
<li><kbd>n</kbd> <?= eL('nexttask') ?></li>
<li><kbd>p</kbd> <?= eL('previoustask') ?></li>
<li><?= $modifier ?> + <kbd>e</kbd> <kbd>↵ Enter</kbd> <?= eL('edittask') ?></li>
<li><?= $modifier ?> + <kbd>w</kbd> <?= eL('watchtask') ?></li>
<li><?= $modifier ?> + <kbd>y</kbd> <?= eL('closetask') ?></li>
</ul>
<h4><?= eL('taskediting') ?></h4>
<ul>
<li><?= $modifier ?> + <kbd>s</kbd> <?= eL('savetask') ?></li>
</ul>
</div>
