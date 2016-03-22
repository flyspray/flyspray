<input type="checkbox" id="s_shortcuts" />
<label for="s_shortcuts" id="shortcutlabel"><i class="fa fa-keyboard-o"></i> <?php echo Filters::noXSS(L('keyboardshortcuts')); ?></label>
<label for="s_shortcuts" id="shortcutsmodal"></label>
<div id="shortcuts">
<label for="s_shortcuts" id="shortcutclose"><i class="fa fa-close fa-2x"></i></label>
<h3><?php echo Filters::noXSS(L('availablekeybshortcuts')); ?></h3>
<h4></h4>
<ul>
<li><kbd>SHIFT+ALT+l</kbd> <?php echo Filters::noXSS(L('logindialoglogout')); ?></li>
<li><kbd>SHIFT+ALT+a</kbd> <?php echo Filters::noXSS(L('addnewtask')); ?></li>
<li><kbd>SHIFT+ALT+m</kbd> <?php echo Filters::noXSS(L('mysearch')); ?></li>
<li><kbd>SHIFT+ALT+t</kbd> <?php echo Filters::noXSS(L('focustaskidsearch')); ?></li>
</ul>
<h4><?php echo Filters::noXSS(L('tasklist')); ?></h4>
<ul>
<li><kbd>o</kbd> <?php echo Filters::noXSS(L('openselectedtask')); ?></li>
<li><kbd>j</kbd> <?php echo Filters::noXSS(L('movecursordown')); ?></li>
<li><kbd>k</kbd> <?php echo Filters::noXSS(L('movecursorup')); ?></li>
</ul>
<h4><?php echo Filters::noXSS(L('taskdetails')); ?></h4>
<ul>
<li><kbd>n</kbd> <?php echo Filters::noXSS(L('nexttask')); ?></li>
<li><kbd>p</kbd> <?php echo Filters::noXSS(L('previoustask')); ?></li>
<li><kbd>SHIFT+ALT+e</kbd> <kbd>ENTER</kbd> <?php echo Filters::noXSS(L('edittask')); ?></li>
<li><kbd>SHIFT+ALT+y</kbd> <?php echo Filters::noXSS(L('closetask')); ?></li>
</ul>
<h4><?php echo Filters::noXSS(L('taskediting')); ?></h4>
<ul>
<li><kbd>SHIFT+ALT+s</kbd> <?php echo Filters::noXSS(L('savetask')); ?></li>
</ul>
</div>
