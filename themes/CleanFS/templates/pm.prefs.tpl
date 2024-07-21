<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('preferences') ?></h2>
<?php echo tpl_form(createUrl('pm', 'prefs', $proj->id)); ?>
<ul id="submenu">
	<li><a href="#general"><span class="fas fa-sliders"></span><span><?= eL('general') ?></span></a></li>
	<li><a href="#lookandfeel"><span class="fas fa-eye"></span><span><?= eL('lookandfeel') ?></span></a></li>
	<li><a href="#notifications"><span class="fas fa-bell"></span><span><?= eL('notifications') ?></span></a></li>
	<li><a href="#feeds"><span class="fas fa-rss"></span><span><?= eL('feeds') ?></span></a></li>
	<li><a href="#effort"><span class="fas fa-hourglass-half"></span><span><?= eL('efforttracking') ?></span></a></li>
</ul>

<div id="general" class="tab">
	<ul class="form_elements">
		<li>
			<label for="projecttitle"><?= eL('projecttitle') ?></label>
			<div class="valuewrap">
			<input id="projecttitle" name="project_title" class="text" type="text" maxlength="100"
				value="<?php echo Filters::noXSS(Post::val('project_title', $proj->prefs['project_title'])); ?>" />
			</div>
		</li>
		<li>
			<label for="defaultcatowner"><?= eL('defaultcatowner') ?></label>
			<div class="valuewrap">
			<?php echo tpl_userselect('default_cat_owner', Post::val('default_cat_owner', $proj->prefs['default_cat_owner']), 'defaultcatowner'); ?>
			</div>
		</li>
		<li>
			<label for="langcode"><?= eL('language') ?></label>
			<div class="valuewrap">
			<select id="langcode" name="lang_code">
			<?php echo tpl_options(array_merge(array('global'), Flyspray::listLangs()), Post::val('lang_code', $proj->prefs['lang_code']), true); ?>
			</select>
			</div>
		</li>
		<li>
			<label for="disp_intro"><?= eL('dispintro') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('disp_intro', Post::val('disp_intro', $proj->prefs['disp_intro']), 'disp_intro'); ?>
			</div>
		</li>
		<li class="wide-element disp_introdep<?php echo ($proj->prefs['disp_intro'] == 1 ? '' : ' hide-intro'); ?>">
			<label class="labeltextarea" for="intromesg"><?= eL('intromessage') ?></label>
			<div class="valuewrap">
				<div class="richtextwrap">
<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
						<div class="hide preview" id="preview"></div>
						<button tabindex="9" type="button" onclick="showPreview('intromesg', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?= eL('preview') ?></button>
<?php endif; ?>
<?php echo TextFormatter::textarea(
				'intro_message',
				8,
				70,
				array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg'),
				Post::val('intro_message', $proj->prefs['intro_message'])
				); ?>
				</div>
			</div>
		</li>

		<li class="wide-element disp_introdep<?php echo ($proj->prefs['disp_intro'] == 1 ? '' : ' hide-intro'); ?>">
			<label class="labeltextarea"><?= eL('pagesintromsg') ?></label>
			<div class="valuewrap">
<?php
	$pages = array(
		'index' => L('tasklist'),
		'toplevel' => L('toplevel'),
		'newmultitasks' => L('addmultipletasks'),
		'details' => L('details'),
		'roadmap' => L('roadmap'),
		'newtask' => L('newtask'),
		'reports' => L('reports'),
		'depends' => L('dependencygraph'),
		'pm' => L('manageproject')
	);

	$selectedPages = explode(' ', $proj->prefs['pages_intro_msg']);
	echo tpl_double_select('pages_intro_msg', $pages, $selectedPages, false, false);
?>
		</div>
		</li>
		<li class="wide-element">
			<label class="labeltextarea" for="default_task"><?= eL('defaulttask') ?></label>
			<div class="valuewrap">
			<div class="richtextwrap">
			<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
			<div class="hide preview" id="preview_taskdesc"></div>
			<button tabindex="9" type="button" onclick="showPreview('default_task', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview_taskdesc')"><?php echo Filters::noXSS(L('preview')); ?></button>
			<?php endif; ?>
			<?php echo TextFormatter::textarea('default_task', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'default_task'), Post::val('default_task', $proj->prefs['default_task'])); ?>
			</div>
			</div>
		</li>
		<li>
			<label for="isactive"><?= eL('isactive') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('project_is_active', Post::val('project_is_active', $proj->prefs['project_is_active']), 'isactive'); ?>
			</div>
		</li>
		<li>
			<label><?= eL('deleteproject') ?></label>
			<div class="valuewrap">
			<div class="valuemulti">
			<?php echo tpl_checkbox('delete_project', null); ?>
			<select name="move_to"><?php echo tpl_options(array_merge(array(0 => L('none')), Flyspray::listProjects()), null, false, null, (string) $proj->id); ?></select>
			</div>
			</div>
		</li>
		<li>
			<label for="othersviewroadmap"><?= eL('othersviewroadmap') ?></label>
			<div class="valuewrap">
			<?php
			# note for FS1.0: This setting is currently also used as anon/public permission for: show project name, activity, stats, milestone progress
			# but not listing tasks per milestone
			echo tpl_checkbox('others_viewroadmap', Post::val('others_viewroadmap', $proj->prefs['others_viewroadmap']), 'othersviewroadmap');
			?>
			</div>
		</li>
		<li>
			<label for="othersview"><?= eL('othersview') ?></label>
			<div class="valuewrap">
			<?php
			# note for FS1.0: This setting is current anon/public task view permission for: listing tasks (toplevel, tasklist, roadmap, RSS feed, ..)
			echo tpl_checkbox('others_view', Post::val('others_view', $proj->prefs['others_view']), 'othersview');
			?>
			</div>
		</li>
		<li>
			<label for="anon_open"><?= eL('allowanonopentask') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('anon_open', Post::val('anon_open', $proj->prefs['anon_open']), 'anon_open'); ?>
			</div>
		</li>
		<li>
			<label for="comment_closed"><?= eL('allowclosedcomments') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('comment_closed', Post::val('comment_closed', $proj->prefs['comment_closed']), 'comment_closed'); ?>
			</div>
		</li>
		<li>
			<label for="auto_assign"><?= eL('autoassign') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('auto_assign', Post::val('auto_assign', $proj->prefs['auto_assign']), 'auto_assign'); ?>
			</div>
		</li>
		<li>
			<label for="defaultdueversion"><?php echo Filters::noXSS(L('defaultdueinversion')); ?></label>
			<div class="valuewrap">
			<select id="defaultdueversion" name="default_due_version">
			<option value="0"><?= eL('undecided') ?></option>
			<?php echo tpl_options($proj->listVersions(false, 3), Post::val('default_due_version', $proj->prefs['default_due_version']), true); ?>
			</select>
			</div>
		</li>
		<li>
			<label for="use_tags"><?= eL('usetags') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('use_tags', Post::val('use_tags', $proj->prefs['use_tags']), 'use_tags'); ?>
			</div>
		</li>
		<li>
			<label for="freetagging"><?= eL('freetagging') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('freetagging', Post::val('freetagging', $proj->prefs['freetagging']), 'freetagging'); ?>
			</div>
		</li>
		<li>
			<label for="use_gantt"><?= eL('usegantt') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('use_gantt', Post::val('use_gantt', $proj->prefs['use_gantt']), 'use_gantt'); ?>
			</div>
		</li>
		<li>
			<label for="use_kanban"><?= eL('usekanban') ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('use_kanban', Post::val('use_kanban', $proj->prefs['use_kanban']), 'use_kanban'); ?>
			</div>
		</li>
	</ul>
</div>

<div id="lookandfeel" class="tab">
	<ul class="form_elements">
		<li>
			<label for="themestyle"><?= eL('themestyle') ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
				<select id="themestyle" name="theme_style">
				<?php echo tpl_options(Flyspray::listThemes(), Post::val('theme_style', $proj->prefs['theme_style']), true); ?>
				</select>
					<div class="valuemultipair">
						<label for="customstyle" style="width:auto"><?= eL('customstyle') ?></label>
						<select id="customstyle" name="custom_style">
<?php
	$customs[]=array('', L('no'));
	$customstyles=glob_compat(BASEDIR ."/themes/".($proj->prefs['theme_style'])."/custom_*.css");

	foreach ($customstyles as $cs){
		$customs[]=array($cs,$cs);
	}

	echo tpl_options($customs, $proj->prefs['custom_style']);
?>
						</select>
					</div>
				</div>
			</div>
		</li>
		<li>
			<label for="default_entry"><?= eL('defaultentry') ?></label>
			<div class="valuewrap">
				<select id="default_entry" name="default_entry">
				<?php echo tpl_options(array('index' => L('tasklist'), 'toplevel' => L('toplevel'), 'roadmap' => L('roadmap')), Post::val('default_entry', $proj->prefs['default_entry'])); ?>
				</select>
			</div>
		</li>

		<?php
			// Set the selectable column names
			// Do NOT use real database column name here and in the next list,
			// but a term from translation table entries instead, because it's
			// also used elsewhere to draw a localized version of the name.
			// Look also at the end of function
			// tpl_draw_cell in scripts/index.php for further explanation.
			$columnnames = array(
				'id' => L('id'),
				'project' => L('project'),
				'parent' => L('parent'),
				'tasktype' => L('tasktype'),
				'category' => L('category'),
				'severity' => L('severity'),
				'priority' => L('priority'),
				'summary' => L('summary'),
				'dateopened' => L('dateopened'),
				'status' => L('status'),
				'openedby' => L('openedby'),
				'private' => L('private'),
				'assignedto' => L('assignedto'),
				'lastedit' => L('lastedit'),
				'editedby' => L('editedby'),
				'reportedin' => L('reportedin'),
				'dueversion' => L('dueversion'),
				'duedate' => L('duedate'),
				'comments' => L('comments'),
				'attachments' => L('attachments'),
				'progress' => L('progress'),
				'dateclosed' => L('dateclosed'),
				'closedby' => L('closedby'),
				'os' => L('os'),
				'votes' => L('votes'),
				'estimatedeffort' => L('estimatedeffort'),
				'effort' => L('effort')
			);
			$selectedcolumns = explode(' ', Post::val('visible_columns', $proj->prefs['visible_columns']));
		?>
		<li>
			<label for="default_order_by"><?php echo Filters::noXSS(L('defaultorderby')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<select id="default_order_by" name="default_order_by">
					<?php echo tpl_options($columnnames, $proj->prefs['sorting'][0]['field'], false); ?>
					</select>
					<!-- <label><?php echo Filters::noXSS(L('defaultorderbydirection')); ?></label> -->
					<select id="default_order_by_dir" name="default_order_by_dir">
					<?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), $proj->prefs['sorting'][0]['dir'], false); ?>
					</select>
				</div>
			</div>
		</li>
		<li>
			<label for="default_order_by2"><?php echo Filters::noXSS(L('defaultorderby2')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<select id="default_order_by2" name="default_order_by2">
					<?php echo tpl_options($columnnames, isset($proj->prefs['sorting'][1]['field']) ? $proj->prefs['sorting'][1]['field'] : null, false); ?>
					</select>
					<select id="default_order_by_dir2" name="default_order_by_dir2">
					<?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), isset($proj->prefs['sorting'][1]['dir']) ? $proj->prefs['sorting'][1]['dir'] : null, false); ?>
					</select>
				</div>
			</div>
		</li>
		<li class="wide-element">
			<label><?= eL('visiblecolumns') ?></label>
			<div class="valuewrap">
			<?php echo tpl_double_select('visible_columns', $columnnames, $selectedcolumns, false); ?>
			</div>
		</li>

		<li class="wide-element">
			<label><?= eL('visiblefields') ?></label>
			<?php
			// Set the selectable field names
			$fieldnames = array(
				'parent' => L('parent'),
				'tasktype' => L('tasktype'),
				'category' => L('category'),
				'severity' => L('severity'),
				'priority' => L('priority'),
				'status' => L('status'),
				'private' => L('private'),
				'assignedto' => L('assignedto'),
				'reportedin' => L('reportedin'),
				'dueversion' => L('dueversion'),
				'duedate' => L('duedate'),
				'progress' => L('progress'),
				'os' => L('os'),
				'votes' => L('votes')
			);
			$selectedfields = explode(' ', Post::val('visible_fields', $proj->prefs['visible_fields']));
			?>
			<div class="valuewrap">
			<?php echo tpl_double_select('visible_fields', $fieldnames, $selectedfields, false); ?>
			</div>
		</li>
	</ul>
</div>

<div id="notifications" class="tab">
	<ul class="form_elements">
		<li>
			<label for="notify_subject"><?php echo Filters::noXSS(L('notifysubject')); ?></label>
			<div class="valuewrap">
			<input id="notify_subject" class="text" name="notify_subject" type="text" value="<?php echo Filters::noXSS(Post::val('notify_subject', $proj->prefs['notify_subject'])); ?>" />
			<span><?php echo Filters::noXSS(L('notifysubjectinfo')); ?></span>
			</div>
		</li>
		<li>
			<label for="emailaddress"><?php echo Filters::noXSS(L('emailaddress')); ?></label>
			<div class="valuewrap">
			<input id="emailaddress" name="notify_email" class="text" type="text" value="<?php echo Filters::noXSS(Post::val('notify_email', $proj->prefs['notify_email'])); ?>" />
			</div>
		</li>
		<?php if (!empty($fs->prefs['jabber_server'])): ?>
		<li>
			<label for="jabberid"><?php echo Filters::noXSS(L('jabberid')); ?></label>
			<div class="valuewrap">
				<input id="jabberid" class="text" name="notify_jabber" type="text" value="<?php echo Filters::noXSS(Post::val('notify_jabber', $proj->prefs['notify_jabber'])); ?>" />
			</div>
		</li>
		<?php endif ?>
		<li>
			<label for="notify_reply"><?php echo Filters::noXSS(L('replyto')); ?></label>
			<div class="valuewrap">
				<input id="notify_reply" name="notify_reply" class="text" type="text" value="<?php echo Filters::noXSS(Post::val('notify_reply', $proj->prefs['notify_reply'])); ?>" />
			</div>
		</li>
		<li>
			<label for="notify_types"><?php echo Filters::noXSS(L('notifytypes')); ?></label>
			<div class="valuewrap">
				<select id="notify_types" size="17" multiple="multiple" name="notify_types[]">
				<?php
					echo tpl_options(
						array(
							0 => L('none'),
							NOTIFY_TASK_OPENED     => L('taskopened'),
							NOTIFY_TASK_CHANGED    => L('pm.taskchanged'),
							NOTIFY_TASK_CLOSED     => L('taskclosed'),
							NOTIFY_TASK_REOPENED   => L('pm.taskreopened'),
							NOTIFY_DEP_ADDED       => L('pm.depadded'),
							NOTIFY_DEP_REMOVED     => L('pm.depremoved'),
							NOTIFY_COMMENT_ADDED   => L('commentadded'),
							NOTIFY_ATT_ADDED       => L('attachmentadded'),
							NOTIFY_REL_ADDED       => L('relatedadded'),
							NOTIFY_OWNERSHIP       => L('ownershiptaken'),
							NOTIFY_PM_REQUEST      => L('pmrequest'),
							NOTIFY_PM_DENY_REQUEST => L('pmrequestdenied'),
							NOTIFY_NEW_ASSIGNEE    => L('newassignee'),
							NOTIFY_REV_DEP         => L('revdepadded'),
							NOTIFY_REV_DEP_REMOVED => L('revdepaddedremoved'),
							NOTIFY_ADDED_ASSIGNEES => L('assigneeadded')
						),
						Post::val('notify_types', Flyspray::int_explode(' ', $proj->prefs['notify_types']))
					);
				?>
				</select>
			</div>
		</li>
	</ul>
</div>

<div id="feeds" class="tab">
	<ul class="form_elements">
		<li>
			<label for="feed_description"><?php echo Filters::noXSS(L('feeddescription')); ?></label>
			<div class="valuewrap">
				<input id="feed_description" class="text" name="feed_description" type="text" value="<?php echo Filters::noXSS(Post::val('feed_description', $proj->prefs['feed_description'])); ?>" />
			</div>
		</li>
		<li>
			<label for="feed_img_url"><?php echo Filters::noXSS(L('feedimgurl')); ?></label>
			<div class="valuewrap">
				<input id="feed_img_url" class="text" name="feed_img_url" type="text" value="<?php echo Filters::noXSS(Post::val('feed_img_url', $proj->prefs['feed_img_url'])); ?>" />
			</div>
		</li>
	</ul>
</div>
<div id="effort" class="tab">
	<ul class="form_elements">
		<li>
			<label for="useeffort"><?php echo Filters::noXSS(L('useeffort')); ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('use_effort_tracking', Post::val('use_effort_tracking', $proj->prefs['use_effort_tracking']), 'useeffort'); ?>
			</div>
		</li>
		<li>
			<label for="hours_per_manday"><?php echo Filters::noXSS(L('hourspermanday')); ?></label>
			<div class="valuewrap">
				<input id="hours_per_manday" class="text" name="hours_per_manday" type="text" value="<?php
					$seconds = Post::val('hours_per_manday', $proj->prefs['hours_per_manday']);
					// Post::val is in HH:mm format, $proj->prefs in seconds.
					if (!preg_match('/^\d+$/', $seconds)) {
						$seconds = effort::EditStringToSeconds($seconds, $proj->prefs['hours_per_manday'], effort::FORMAT_HOURS_COLON_MINUTES);
					}

					echo Filters::noXSS(effort::SecondsToEditString($seconds,$proj->prefs['hours_per_manday'], effort::FORMAT_HOURS_COLON_MINUTES));
				?>" />
			</div>
		</li>
		<li>
			<label for="estimated_effort_format"><?php echo Filters::noXSS(L('estimatedeffortformat')); ?></label>
			<div class="valuewrap">
				<select id="estimated_effort_format" name="estimated_effort_format">
				<?php
					echo tpl_options(
						array(
							effort::FORMAT_HOURS_COLON_MINUTES => L('hourplural') . ':' . L('minuteplural'),
							effort::FORMAT_HOURS_SPACE_MINUTES => L('hourplural') . ' ' . L('minuteplural'),
							effort::FORMAT_HOURS_PLAIN => L('hourplural'),
							effort::FORMAT_HOURS_ONE_DECIMAL => L('hourplural') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_MINUTES => L('minuteplural'),
							effort::FORMAT_DAYS_PLAIN => L('mandays'),
							effort::FORMAT_DAYS_ONE_DECIMAL => L('mandays') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_DAYS_PLAIN_HOURS_PLAIN => L('mandays') . ' ' . L('hourplural'),
							effort::FORMAT_DAYS_PLAIN_HOURS_ONE_DECIMAL => L('mandays') . ' ' . L('hourplural') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_DAYS_PLAIN_HOURS_COLON_MINUTES => L('mandays') . ' ' . L('hourplural') . ":" . L('minuteplural'),
							effort::FORMAT_DAYS_PLAIN_HOURS_SPACE_MINUTES => L('mandays') . ' ' . L('hourplural') . " " . L('minuteplural'),
						),
						Post::val('estimated_effort_format', $proj->prefs['estimated_effort_format'])
					);
				?>
				</select>
			</div>
		</li>
		<li>
			<label for="current_effort_done_format"><?php echo Filters::noXSS(L('currenteffortdoneformat')); ?></label>
			<div class="valuewrap">
				<select id="current_effort_done_format" name="current_effort_done_format">
				<?php
					echo tpl_options(
						array(
							effort::FORMAT_HOURS_COLON_MINUTES => L('hourplural') . ':' . L('minuteplural'),
							effort::FORMAT_HOURS_SPACE_MINUTES => L('hourplural') . ' ' . L('minuteplural'),
							effort::FORMAT_HOURS_PLAIN => L('hourplural'),
							effort::FORMAT_HOURS_ONE_DECIMAL => L('hourplural') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_MINUTES => L('minuteplural'),
							effort::FORMAT_DAYS_PLAIN => L('mandays'),
							effort::FORMAT_DAYS_ONE_DECIMAL => L('mandays') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_DAYS_PLAIN_HOURS_PLAIN => L('mandays') . ' ' . L('hourplural'),
							effort::FORMAT_DAYS_PLAIN_HOURS_ONE_DECIMAL => L('mandays') . ' ' . L('hourplural') . ' (' . L('onedecimal') . ')',
							effort::FORMAT_DAYS_PLAIN_HOURS_COLON_MINUTES => L('mandays') . ' ' . L('hourplural') . ":" . L('minuteplural'),
							effort::FORMAT_DAYS_PLAIN_HOURS_SPACE_MINUTES => L('mandays') . ' ' . L('hourplural') . " " . L('minuteplural'),
						),
						Post::val('current_effort_done_format', $proj->prefs['current_effort_done_format'])
					);
				?>
				</select>
			</div>
		</li>
	</ul>
</div>

<div class="buttons">
	<input type="hidden" name="action" value="pm.updateproject" />
	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<button type="submit" class="positive"><?php echo Filters::noXSS(L('saveoptions')); ?></button>
	<button type="reset"><?php echo Filters::noXSS(L('resetoptions')); ?></button>
</div>
</form>
</div>
