<input id="menu1" type="checkbox">
<label id="labelmenu1" for="menu1"><span class="fas fa-gear fa-2x"></span></label>
<div id="menu"><ul id="menu-list"><?php
if ($user->isAnon()):
	# 20150211 peterdd: pure css toggle using checked status, no js needed
	?><li class="first">
	<input type="checkbox" id="s_loginbox" />
        <label for="s_loginbox" id="show_loginbox" accesskey="l"><?= eL('login') ?></label>
        <div id="loginbox" class="popup"><?php $this->display('loginbox.tpl'); ?></div>
	</li><?php
else: ?><li>
		<a id="profilelink" <?php if($do == 'myprofile'): ?> class="active"<?php endif; ?> href="<?php echo Filters::noXSS(createURL('myprofile')); ?>" title="<?php echo Filters::noXSS(L('editmydetails')); ?> <?php echo Filters::noXSS($user->infos['real_name']); ?> (<?php echo Filters::noXSS($user->infos['user_name']); ?>)"><span class="fas fa-user fa-lg"></span></a>
	</li><li>
		<a id="lastsearchlink" href="#" accesskey="m" onclick="showhidestuff('mysearches');return false;" class="inactive"><?= eL('mysearch') ?></a>
		<div id="mysearches"><?php $this->display('links.searches.tpl'); ?></div>
	</li><?php
	if ($user->perms('is_admin')):
	?><li>
		<a id="optionslink"<?php if ($do=='admin'): ?> class="active"<?php endif; ?> href="<?php echo Filters::noXSS(createURL('admin', 'prefs')); ?>" title="<?= eL('admintoolbox') ?>"><span class="fas fa-gears fa-lg"></span></a>
	</li><?php
	endif;
	?><li>
		<a id="logoutlink" href="<?php echo Filters::noXSS(createURL('logout', null)); ?>"
		accesskey="l" title="<?= eL('logout') ?>"><span class="fas fa-power-off fa-lg"></span></a>
	</li><?php
	if (isset($_SESSION['was_locked'])):
	?><li>
		<span id="locked"><?= eL('accountwaslocked') ?></span>
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
	<input id="pmmenu" type="checkbox">
	<label id="labelpmmenu" for="pmmenu"><span class="fas fa-bars fa-2x"></span></label>
	<ul id="pm-menu-list"><?php
	if ( count($fs->projects) && $user->can_select_project($proj->id) ) {
	?><li class="first">
		<a id="toplevellink"
		<?php if($do == 'toplevel'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('toplevel', $proj->id)); ?>"><span class="far fa-folder-closed fa-lg"></span><?= eL('overview') ?></a>
	</li><?php
	}
	if( (!$user->isAnon() && $user->perms('view_tasks')) || ($user->isAnon() && $proj->id >0 && $proj->prefs['others_view'])):
	?><li>
		<a id="homelink"
		<?php if($do == 'index' && !(isset($_GET['dev']) && !$user->isAnon() && $_GET['dev'] == $user->id)): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('tasklist', $proj->id)); ?>"><span class="fas fa-list-check fa-lg"></span><?= eL('tasklist') ?></a>
	</li><?php
	endif;
	if($proj->id && $user->perms('open_new_tasks')):
	?><li>
		<a id="newtasklink" href="<?php echo Filters::noXSS(createURL('newtask', $proj->id)); ?>"
		<?php if($do == 'newtask'): ?> class="active" <?php endif; ?>
		accesskey="a"><span class="far fa-file fa-lg"></span><?= eL('addnewtask') ?></a>
	</li><?php
	if($proj->id && $user->perms('add_multiple_tasks')) :
	?><li>
		<a id="newmultitaskslink" href="<?php echo Filters::noXSS(createURL('newmultitasks', $proj->id)); ?>"
		<?php if($do == 'newmultitasks'): ?> class="active"<?php endif; ?>><span class="far fa-copy fa-lg"></span><?= eL('addmultipletasks') ?></a>
	</li><?php
	endif;
	elseif ($proj->id && $user->isAnon() && $proj->prefs['anon_open'] && $proj->prefs['project_is_active']): ?><li>
		<a id="anonopen"
		<?php if($do == 'newtask'): ?> class="active" <?php endif; ?>
		href="?do=newtask&amp;project=<?php echo Filters::noXSS($proj->id); ?>"><span class="far fa-file fa-lg"></span><?= eL('opentaskanon') ?></a>
	</li><?php
	endif;
	if(!$user->isAnon()): ?><li>
		<a id="mytaskslink"
		<?php if($do == 'index' && isset($_GET['dev']) && $_GET['dev'] == $user->id): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('mytasks', $proj->id, $user->id, null)); ?>"><span class="fas fa-inbox fa-lg"></span><?= eL('myassignedtasks') ?></a>
	</li><?php
	endif;
	if($user->perms('view_reports')): ?><li>
		<a id="reportslink"
		<?php if( $do == 'reports'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('reports', $proj->id)); ?>"><span class="fas fa-book fa-lg"></span><?= eL('reports') ?></a>
	</li><?php
	endif;
	if($proj->id && ($user->perms('view_roadmap') || ($user->isAnon() && $proj->prefs['others_viewroadmap'])) ): ?><li>
		<a id="roadmaplink"
		<?php if($do == 'roadmap'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('roadmap', $proj->id)); ?>"><span class="far fa-map fa-lg"></span><?= eL('roadmap') ?></a>
	</li><?php
	endif;
	if(
		file_exists(BASEDIR . '/scripts/gantt.php')
		&& $proj->id
		&& $user->perms('view_roadmap')
		&& (isset($proj->prefs['use_gantt']) && $proj->prefs['use_gantt'])        
	): ?><li><a id="gantt"
		<?php if($do == 'gantt'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('gantt', $proj->id)); ?>" title="<?= eL('gantt') ?>"><span class="fas fa-bars-progress fa-lg"></span><?= eL('gantt'); ?></a>
		</li><?php
	endif;
	if(
		file_exists(BASEDIR . '/scripts/kanban.php')
		&& $proj->id
		&& $user->perms('view_roadmap')	
		&& (isset($proj->prefs['use_kanban']) && $proj->prefs['use_kanban'])
	): ?><li><a id="kanban"
		<?php if($do == 'kanban'): ?> class="active" <?php endif; ?>
		href="<?php echo Filters::noXSS(createURL('kanban', $proj->id)); ?>" title="<?= eL('kanban') ?>"><span class="fas fa-table-columns fa-lg"></span><?= eL('kanban') ?></a>
		</li><?php
	endif;
	if ($proj->id && $user->perms('manage_project')): ?><li>
		<a id="projectslink"<?php if($do=='pm'): ?> class="active"<?php endif; ?> href="<?php echo Filters::noXSS(createURL('pm', 'prefs', $proj->id)); ?>"><span class="fas fa-gear fa-lg"></span><?= eL('manageproject') ?></a>
	</li><?php
	endif;
	if ($proj->id && isset($pm_pendingreq_num) && $pm_pendingreq_num):
	?><li>
		<a class="pendingreq attention"
		href="<?php echo Filters::noXSS(createURL('pm', 'pendingreq', $proj->id)); ?>"><span class="pendingreq"><?php echo Filters::noXSS($pm_pendingreq_num); ?></span> <?= eL('pendingreq') ?></a>
		</li><?php
	endif;
	if ($user->perms('is_admin') && isset($admin_pendingreq_num) && $admin_pendingreq_num):
	?><li>
		<a class="pendingreq attention"
		href="<?php echo Filters::noXSS(createURL('admin', 'userrequest')); ?>"><span class="pendingreq"><?php echo Filters::noXSS($admin_pendingreq_num); ?></span> <?= eL('adminrequestswaiting') ?></a>
	</li><?php
	endif; ?>
	</ul>
	<div id="pmcontrol">
		<div id="projectselector"><?php
                # $fs->projects is filtered with can_select_project() for the current user/guest in index.php
                if(count($fs->projects)>0): ?>
			<form id="projectselectorform" action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
				<select name="project" onchange="document.getElementById('projectselectorform').submit()">
				<?php echo tpl_options(array_merge(array(0 => L('allprojects')), $fs->projects), $proj->id); ?>
				</select>
				<noscript><button type="submit"><?= eL('switch') ?></button></noscript>
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
		<?php endif; ?></div>
		<div id="showtask"><?php
                # $fs->projects is filtered with can_select_project() for the current user/guest in index.php
                if(count($fs->projects)>0): ?>
			<form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
				<noscript><button type="submit"><?= eL('showtask') ?> #</button></noscript>
				<input id="task_id" name="show_task" class="text" type="text" size="10" accesskey="t" placeholder="<?= eL('showtask') ?> #" />
			</form>
		<?php endif; ?></div>
	</div>
</div>
