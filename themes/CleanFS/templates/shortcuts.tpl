<?php
/*
TODO: show only or at least highlight the section that is matching the current view.
TODO: a fast platform and browser detection of user agent string to dynamically adapt accesskey help,
see https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/accesskey
if ($is_mac) {
$accesskey='<kbd>CTRL</kbd> + <kbd>ALT</kbd>';
} elseif ($is_firefox) {
	$accesskey='<kbd>ALT</kbd> + <kbd>SHIFT</kbd>';
} else {
	$accesskey='<kbd>ALT</kbd>';
}
*/

$accesskey='<kbd>CTRL</kbd> + <kbd>ALT</kbd>';

?>
<input type="checkbox" id="s_shortcuts" />
<label for="s_shortcuts" id="shortcutlabel"><i class="fa fa-keyboard-o"></i> <?= eL('keyboardshortcuts') ?></label>
<label for="s_shortcuts" id="shortcutsmodal"></label>
<div id="shortcuts">
<label for="s_shortcuts" id="shortcutclose"><i class="fa fa-close fa-2x"></i></label>
<h3><?= eL('availablekeybshortcuts') ?></h3>
<h4></h4>
<ul>
<li><?= $accesskey ?> + <kbd>l</kbd> <?= eL('logindialoglogout') ?></li>
<li><?= $accesskey ?> + <kbd>a</kbd> <?= eL('addnewtask') ?></li>
<li><?= $accesskey ?> + <kbd>m</kbd> <?= eL('mysearch') ?></li>
<li><?= $accesskey ?> + <kbd>t</kbd> <?= eL('focustaskidsearch') ?></li>
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
<li><?= $accesskey ?> + <kbd>e</kbd> <kbd>ENTER</kbd> <?= eL('edittask') ?></li>
<li><?= $accesskey ?> + <kbd>w</kbd> <?= eL('watchtask') ?></li>
<li><?= $accesskey ?> + <kbd>y</kbd> <?= eL('closetask') ?></li>
</ul>
<h4><?= eL('taskediting') ?></h4>
<ul>
<li><?= $accesskey ?> + <kbd>s</kbd> <?= eL('savetask') ?></li>
</ul>
</div>
