<div id="menu">

		<ul id="menu-list">
<?php if ($user->isAnon()): ?>
			<li class="first">
				<a id="show_loginbox" accesskey="l" href="#login" onclick="this.addClassName('active'); showhidestuff('loginbox');return false;">{L('login')}</a>
				<div id="loginbox" class="popup hide">
		    <?php $this->display('loginbox.tpl'); ?>
			</div></li>
      <?php else: ?>
			<li class="first" onmouseover="perms.do_later('show')" onmouseout="perms.hide()">
				<a id="profilelink"
					<?php if(isset($_GET['do']) and $_GET['do'] == 'myprofile'): ?> class="active" <?php endif; ?>
					 href="{CreateURL('myprofile')}" title="{L('editmydetails')}">
					<em>{$user->infos['real_name']} ({$user->infos['user_name']})</em>
				</a>
				<div id="permissions">
					{!tpl_draw_perms($user->perms)}
				</div>
			</li>
			<li>
				<a id="lastsearchlink" href="#" accesskey="m" onclick="showhidestuff('mysearches');return false;" class="inactive">{L('mysearch')}</a>
				<div id="mysearches">
					<?php $this->display('links.searches.tpl'); ?>
				</div>
			</li>
		<?php if ($user->perms('is_admin')): ?>
			<li>
				<a id="optionslink"
					<?php if(isset($_GET['do']) and $_GET['do'] == 'admin'): ?> class="active" <?php endif; ?>
					 href="{CreateURL('admin', 'prefs')}">{L('admintoolbox')}</a>
			</li>
		<?php endif; ?>
		
			<li>
				<a id="logoutlink" href="{CreateURL('logout', null)}"
					accesskey="l">{L('logout')}</a>
			</li>
			<?php if (isset($_SESSION['was_locked'])): ?>
			<li>
				<span id="locked">{L('accountwaslocked')}</span>
			</li>
			<?php elseif (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0): ?>
			<li>
				<span id="locked">{sprintf(L('failedattempts'), $_SESSION['login_attempts'])}</span>
			</li>
			<?php endif; unset($_SESSION['login_attempts'], $_SESSION['was_locked']); ?>
		
		
<?php endif; ?>
</ul>
</div>

<div id="pm-menu">

	<ul id="pm-menu-list">
		<li class="first">
			<a id="toplevellink"
					<?php if(isset($_GET['do']) and $_GET['do'] == 'toplevel'): ?> class="active" <?php endif; ?>
				 href="{CreateURL('toplevel', $proj->id)}">{L('overview')}</a>
		</li>

		<li>
		<a id="homelink"
				<?php if(!isset($_GET['do']) or $_GET['do'] == 'index'): ?> class="active" <?php endif; ?>
				href="{CreateURL('project', $proj->id, null, array('do' => 'index'))}">{L('tasklist')}</a>
		</li>

		<?php if ($proj->id && $user->perms('open_new_tasks')): ?>
			<li>
			<a id="newtasklink" href="{CreateURL('newtask', $proj->id)}"
				<?php if(isset($_GET['do']) and $_GET['do'] == 'newtask'): ?> class="active" <?php endif; ?>
				accesskey="a">{L('addnewtask')}</a>
			</li>
		<?php elseif ($proj->id && $user->isAnon() && $proj->prefs['anon_open']): ?>
			<li>
				<a id="anonopen"
				<?php if(isset($_GET['do']) and $_GET['do'] == 'newtask'): ?> class="active" <?php endif; ?>
					href="?do=newtask&amp;project={$proj->id}">{L('opentaskanon')}</a>
			</li>
		<?php endif; ?>

		<?php if ($user->perms('view_reports')): ?>
			<li>
			<a id="reportslink"
				<?php if(isset($_GET['do']) and $_GET['do'] == 'reports'): ?> class="active" <?php endif; ?>
				 href="{CreateURL('reports', null, null, array('project' => $proj->id))}">{L('reports')}</a>
			</li>
		<?php endif; ?>

		<?php if ($proj->id): ?>
		<li>
		<a id="roadmaplink"
				<?php if(isset($_GET['do']) and $_GET['do'] == 'roadmap'): ?> class="active" <?php endif; ?>
				href="{CreateURL('roadmap', $proj->id)}">{L('roadmap')}</a>
		</li>
		<?php endif; ?>

		<?php if ($proj->id && $user->perms('manage_project')): ?>
			<li>
			<a id="projectslink"
				<?php if(isset($_GET['do']) and $_GET['do'] == 'pm'): ?> class="active" <?php endif; ?>
				href="{CreateURL('pm', 'prefs', $proj->id)}">{L('manageproject')}</a>
			</li>
		<?php endif; ?>

		<?php if ($proj->id && isset($pm_pendingreq_num) && $pm_pendingreq_num): ?>
			<li>
				<a class="pendingreq attention"
					 href="{CreateURL('pm', 'pendingreq', $proj->id)}">{$pm_pendingreq_num} {L('pendingreq')}</a>
			</li>
		<?php endif; ?>
		
		<li id="pmcontrol">
			<div id="projectselector">
				<form id="projectselectorform" action="{$baseurl}index.php" method="get">
					 <div>
						<select name="project" onchange="document.getElementById('projectselectorform').submit()">
							{!tpl_options(array_merge(array(0 => L('allprojects')), $fs->projects), $proj->id)}
						</select>
						<button type="submit">{L('switch')}</button>
						<input type="hidden" name="do" value="{$do}" />
						<input type="hidden" value="1" name="switch" />
						<?php $check = array('area', 'id');
									if ($do == 'reports') {
										$check = array_merge($check, array('open', 'close', 'edit', 'assign', 'repdate', 'comments', 'attachments',
																		'related', 'notifications', 'reminders', 'within', 'duein', 'fromdate', 'todate'));
									}
									foreach ($check as $key):
									if (Get::has($key)): ?>
						<input type="hidden" name="{$key}" value="{Get::val($key)}" />
						<?php endif;
									endforeach; ?>
					</div>
				</form>	
			</div>
			<div id="showtask">
				<form action="{$baseurl}index.php" method="get">
					<div>
						<button type="submit">{L('showtask')} #</button>
						<input id="taskid" name="show_task" class="text" type="text" size="10" accesskey="t" />
					</div>
				</form>
			</div>

		</li>
		
	</ul>
	
</div>
