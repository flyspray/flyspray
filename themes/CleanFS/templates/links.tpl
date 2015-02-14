<div id="menu"><ul id="menu-list"><?php
if ($user->isAnon()):
	# 20150211 peterdd: pure css toggle using checked status, no js needed
	?><li class="first">
	<input type="checkbox" id="s_loginbox" />
        <label for="s_loginbox" id="show_loginbox" accesskey="l"><?php echo Filters::noXSS(L('login')); ?></label>
        <div id="loginbox" class="popup"><?php $this->display('loginbox.tpl'); ?></div>
	</li><?php
else:
	?><li class="first" onmouseover="perms.do_later('show')" onmouseout="perms.hide()">
		<a id="profilelink"
			<?php if(isset($_GET['do']) and $_GET['do'] == 'myprofile'): ?> class="active" <?php endif; ?>
			 href="<?php echo Filters::noXSS(CreateURL('myprofile')); ?>" title="<?php echo Filters::noXSS(L('editmydetails')); ?>">
			<em><?php echo Filters::noXSS($user->infos['real_name']); ?> (<?php echo Filters::noXSS($user->infos['user_name']); ?>)</em>
		</a>
		<div id="permissions"><?php echo tpl_draw_perms($user->perms); ?></div>
	</li><li>
		<a id="lastsearchlink" href="#" accesskey="m" onclick="showhidestuff('mysearches');return false;" class="inactive"><?php echo Filters::noXSS(L('mysearch')); ?></a>
		<div id="mysearches"><?php $this->display('links.searches.tpl'); ?></div>
	</li><?php
	if ($user->perms('is_admin')):
	?><li>
		<a id="optionslink"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'admin'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('admin', 'prefs')); ?>"><?php echo Filters::noXSS(L('admintoolbox')); ?></a>
	</li><?php 
	endif;
	?><li>
		<a id="logoutlink" href="<?php echo Filters::noXSS(CreateURL('logout', null)); ?>"
		accesskey="l"><?php echo Filters::noXSS(L('logout')); ?></a>
	</li><?php
	if (isset($_SESSION['was_locked'])):
	?><li>
		<span id="locked"><?php echo Filters::noXSS(L('accountwaslocked')); ?></span>
	</li><?php
	elseif (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0):
	?><li>
		<span id="locked"><?php echo Filters::noXSS(sprintf(L('failedattempts'), $_SESSION['login_attempts'])); ?></span>
	</li><?php
	endif;
	unset($_SESSION['login_attempts'], $_SESSION['was_locked']);
		
endif; ?>
</ul>
</div><div id="pm-menu">
	<ul id="pm-menu-list"><?php 
	if ( count($fs->projects) ) {
	?><li class="first">
		<a id="toplevellink"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'toplevel'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('toplevel', $proj->id)); ?>"><?php echo Filters::noXSS(L('overview')); ?></a>
	</li><?php 
	}
	?><li>
		<a id="homelink"
		<?php if(!isset($_GET['do']) or $_GET['do'] == 'index'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('project', $proj->id, null, array('do' => 'index'))); ?>"><?php echo Filters::noXSS(L('tasklist')); ?></a>
	</li><?php 
	if ($proj->id && $user->perms('open_new_tasks')):
	?><li>
		<a id="newtasklink" href="<?php echo Filters::noXSS(CreateURL('newtask', $proj->id)); ?>"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'newtask'): ?> class="active" <?php endif; ?>
		accesskey="a"><?php echo Filters::noXSS(L('addnewtask')); ?></a>
	</li><li>
		<a id="newmultitaskslink" href="<?php echo Filters::noXSS(CreateURL('newmultitasks', $proj->id)); ?>"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'newmultitasks'): ?> class="active" <?php endif; ?>
		accesskey="a"><?php echo Filters::noXSS(L('addmultipletasks')); ?></a>
		</li><?php 
	elseif ($proj->id && $user->isAnon() && $proj->prefs['anon_open']):
	?><li>
		<a id="anonopen"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'newtask'): ?> class="active" <?php endif; ?>
		href="?do=newtask&amp;project=<?php echo Filters::noXSS($proj->id); ?>"><?php echo Filters::noXSS(L('opentaskanon')); ?></a>
	</li><?php
	endif;
	if ($user->perms('view_reports')): ?><li>
		<a id="reportslink"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'reports'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('reports', null, null, array('project' => $proj->id))); ?>"><?php echo Filters::noXSS(L('reports')); ?></a>
	</li><?php 
	endif;
	if ($proj->id): ?><li>
		<a id="roadmaplink"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'roadmap'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('roadmap', $proj->id)); ?>"><?php echo Filters::noXSS(L('roadmap')); ?></a>
	</li>
	<?php
	endif;
	if ($proj->id && $user->perms('manage_project')):
	?><li>
		<a id="projectslink"
		<?php if(isset($_GET['do']) and $_GET['do'] == 'pm'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(CreateURL('pm', 'prefs', $proj->id)); ?>"><?php echo Filters::noXSS(L('manageproject')); ?></a>
	</li>
	<?php
	endif;
	if ($proj->id && isset($pm_pendingreq_num) && $pm_pendingreq_num):
	?><li>
		<a class="pendingreq attention"
		href="<?php echo Filters::noXSS(CreateURL('pm', 'pendingreq', $proj->id)); ?>"><?php echo Filters::noXSS($pm_pendingreq_num); ?> <?php echo Filters::noXSS(L('pendingreq')); ?></a>
		</li><?php
	endif;
	if (!$proj->id && $user->perms('is_admin') && isset($admin_pendingreq_num) && $admin_pendingreq_num):
	?><li>
		<a class="pendingreq attention"
		href="<?php echo Filters::noXSS(CreateURL('admin', 'userrequest')); ?>"><?php echo Filters::noXSS($admin_pendingreq_num); ?> <?php echo Filters::noXSS(L('adminrequestswaiting')); ?></a>
	</li><?php
	endif; ?>
	</ul>
	<div id="pmcontrol">
		<div id="projectselector">
			<form id="projectselectorform" action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
				<select name="project" onchange="document.getElementById('projectselectorform').submit()">
				<?php echo tpl_options(array_merge(array(0 => L('allprojects')), $fs->projects), $proj->id); ?>
				</select>
				<button type="submit"><?php echo Filters::noXSS(L('switch')); ?></button>
				<input type="hidden" name="do" value="<?php echo Filters::noXSS($do); ?>" />
				<input type="hidden" value="1" name="switch" />
				<?php $check = array('area', 'id');
				if ($do == 'reports') {
					$check = array_merge($check, array('open', 'close', 'edit', 'assign', 'repdate', 'comments', 'attachments',
							'related', 'notifications', 'reminders', 'within', 'duein', 'fromdate', 'todate'));
				}
				foreach ($check as $key):
					if (Get::has($key)): ?>
					<input type="hidden" name="<?php echo Filters::noXSS($key); ?>" value="<?php echo Filters::noXSS(Get::val($key)); ?>" />
					<?php endif;
				endforeach; ?>
			</form>	
		</div>
		<div id="showtask">
			<form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
				<button type="submit"><?php echo Filters::noXSS(L('showtask')); ?> #</button>
				<input id="task_id" name="show_task" class="text" type="text" size="10" accesskey="t" />
			</form>
		</div>
	</div>
</div>
