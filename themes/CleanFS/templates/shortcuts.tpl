<input type="checkbox" id="s_shortcuts" />
<label for="s_shortcuts" id="shortcutlabel"><i class="fa fa-keyboard-o"></i> <?php echo Filters::noXSS(L('keyboardshortcuts')); ?></label>
<div id="shortcuts">
<label for="s_shortcuts" id="shortcutclose"><i class="fa fa-close fa-2x"></i></label>
<h3>Available keyboard shortcuts</h3>
<h4></h4>
<ul>
<li><kbd>SHIFT+ALT+l</kbd> Login Dialog / Logout</li>
<li><kbd>SHIFT+ALT+a</kbd> Add a new task</li>
<li><kbd>SHIFT+ALT+m</kbd> My Search Profiles</li>
<li><kbd>SHIFT+ALT+t</kbd> Focus taskid search</li>
</ul>
<h4>Task List</h4>
<ul>
<li><kbd>o</kbd> open selected task</li>
<li><kbd>j</kbd> move cursor down</li>
<li><kbd>k</kbd> move cursor up</li>
</ul>
<h4>Task Details</h4>
<ul>
<li><kbd>n</kbd> next task</li>
<li><kbd>p</kbd> previous task</li>
</ul>
<h4>Add/Edit Task</h4>
<ul>
<li><kbd>SHIFT+ALT+s</kbd> Save task</li>
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
