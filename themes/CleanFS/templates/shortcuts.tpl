<input type="checkbox" id="s_shortcuts" />
<label for="s_shortcuts" id="shortcutlabel"><i class="fa fa-keyboard-o"></i> <?php echo Filters::noXSS(L('keyboardshortcuts')); ?></label>
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
</ul>
<h4><?php echo Filters::noXSS(L('taskediting')); ?></h4>
<ul>
<li><kbd>SHIFT+ALT+s</kbd> <?php echo Filters::noXSS(L('savetask')); ?></li>
</ul>
TODO:complete the list<br /> for accesskey usage different shortcuts on Windows, Mac, Linux .., currently shown for Firefox
</div>
<style>
#shortcutlabel { cursor:pointer; }
#shortcutclose { cursor:pointer;float:right; }
#shortcuts {
  display:none;
  position:fixed;
  z-index:100;
  background:#fff;
  border:1px solid #999;
  border-radius:10px;
  padding:10px;
  box-shadow:0 0 400px #000;
  top:50%;
  height:500px;
  margin-top:-250px;
  left:50%;
  width:300px;
  margin-left:-150px;
}
#s_shortcuts {display:none;}
#s_shortcuts:checked ~ #shortcuts {
  display: block;
}
</style>
