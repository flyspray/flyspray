<?php echo tpl_form(Filters::noXSS(createUrl('details', $task_details['task_id'])),null,null,null,'id="taskeditform"'); ?>
<!-- Grab fields wanted for this project so we can only show those we want -->
<?php $fields = explode( ' ', $proj->prefs['visible_fields'] ); 
# FIXME The template should respect the ordering of 'visible_fields', aren't they?
# Maybe define a 'put visible_fields in default ordering'-button in project settings to let them make consistent with other projects and a no-brainer.
# But let also project managers have the choice to sort to the order they want it.

# FIXME If user wants a task to be moved to other project and a hidden list value (not in visible_fields) would be not legal in the target project:
# Should we show that dropdown-list even if the field is not in the $fields-array to give the user the chance to resolve the issue?
# The field list dropdown is not a secret for webtech-people, it is just not visible by css display:none;
?>
<style>
/* can be moved to default theme.css later, when the multiple errors/messages-feature is matured. currently used only here. */
.errorinput{color:#c00 !important;}
li.errorinput{background-color:#fc9;}
.errorinput::before{display:block;content: attr(title);}
</style>
<div id="taskdetails">
	<input type="hidden" name="action" value="details.update" />
	<input type="hidden" name="edit" value="1" />
	<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
	<input type="hidden" name="edit_start_time" value="<?php echo Filters::noXSS(Req::val('edit_start_time', time())); ?>" />
	<div id="taskfields">
	<ul class="form_elements slim">
	<!-- Status -->
	<li<?php
		# show the tasktype if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invalidstatus']) ? ' class="errorinput"' : (in_array('status', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidstatus']) ? '<span class="errorinput" style="display:block;">'.eL('invalidstatus').'</span>' : ''; ?>
		<label for="status"><?= eL('status') ?></label>
		<?php echo tpl_select($statusselect); ?>
	</li>
	<!-- Progress -->
	<li<?php echo in_array('progress', $fields) ? '' : ' style="display:none"'; ?>>
		<label for="percent"<?php echo isset($_SESSION['ERRORS']['invalidprogress']) ? ' class="errorinput" title="'.eL('invalidprogress').'"':''; ?>><?php echo Filters::noXSS(L('percentcomplete')); ?></label>
		<select id="percent" name="percent_complete" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')) ?>>
		<?php $arr = array(); for ($i = 0; $i<=100; $i+=10) $arr[$i] = $i.'%'; ?>
		<?php echo tpl_options($arr, Req::val('percent_complete', $task_details['percent_complete'])); ?>
		</select>
	</li>
	<!-- Task Type -->
	<li<?php
		# show the tasktype if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invalidtasktype']) ? ' class="errorinput"' : (in_array('tasktype', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidtasktype']) ? '<span class="errorinput" style="display:block;">'.eL('invalidtasktype').'</span>' : ''; ?>
		<label for="tasktype"><?= eL('tasktype') ?></label>
		<?php echo tpl_select($tasktypeselect); ?>
	</li>
	<!-- Category -->
	<li<?php
		# show the category if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invalidcategory']) ? ' class="errorinput"' : (in_array('category', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidcategory']) ? '<span class="errorinput" style="display:block;">'.eL('invalidcategory').'</span>' : ''; ?>
		<label for="category"><?= eL('category') ?></label>
		<?php echo tpl_select($catselect); ?>
	</li>
	<!-- Assigned To -->
	<li<?php echo in_array('assignedto', $fields) ? '' : ' style="display:none"'; ?>>
		<label><?= eL('assignedto') ?></label>
		<?php if ($user->perms('edit_assignments')): ?>
			<input type="hidden" name="old_assigned" value="<?php echo Filters::noXSS($old_assigned); ?>" />
		<?php $this->display('common.multiuserselect.tpl'); ?>
		<?php else: ?>
			<?php if (empty($assigned_users)): ?>
				<?= eL('noone') ?>
			<?php else:
				foreach ($assigned_users as $userid): ?>
					<?php echo tpl_userlink($userid); ?><br />
				<?php endforeach;
			endif;
		endif; ?>
	</li>
	<!-- OS -->
	<li<?php
		# show the os if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invalidos']) ? ' class="errorinput"' : (in_array('os', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidos']) ? '<span class="errorinput" style="display:block;">'.eL('invalidos').'</span>' : ''; ?>
		<label for="os"><?= eL('operatingsystem') ?></label>
		<?php echo tpl_select($osselect); ?>
	</li>
	<!-- Severity -->
	<li<?php echo in_array('severity', $fields) ? '' : ' style="display:none"'; ?>>
		<label for="severity"<?php echo isset($_SESSION['ERRORS']['invalidseverity']) ? ' class="errorinput" title="'.eL('invalidseverity').'"':''; ?>><?php echo Filters::noXSS(L('severity')); ?></label>
		<select id="severity" name="task_severity">
		 <?php echo tpl_options($fs->severities, Req::val('task_severity', $task_details['task_severity'])); ?>
		</select>
	</li>
	<!-- Priority -->
	<li<?php echo in_array('priority', $fields) ? '' : ' style="display:none"'; ?>>
		<label for="priority"<?php echo isset($_SESSION['ERRORS']['invalidpriority']) ? ' class="errorinput" title="'.eL('invalidpriority').'"':''; ?>><?php echo Filters::noXSS(L('priority')); ?></label>
		<select id="priority" name="task_priority" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')) ?>>
		<?php echo tpl_options($fs->priorities, Req::val('task_priority', $task_details['task_priority'])); ?>
		</select>
	</li>
	<!-- Reported In -->
	<li<?php
		# show the reportedversion if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invalidreportedversion']) ? ' class="errorinput"' : (in_array('reportedin', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidreportedversion']) ? '<span class="errorinput" style="display:block;">'.eL('invalidreportedversion').'</span>' : ''; ?>
		<label for="reportedver"><?= eL('reportedversion') ?></label>
		<?php echo tpl_select($reportedversionselect); ?>
	</li>
	<!-- Due Version -->
	<li<?php
		# show the dueversion if invalid when moving tasks - even if not in the visible list.
		echo isset($_SESSION['ERRORS']['invaliddueversion']) ? ' class="errorinput"' : (in_array('dueversion', $fields) ? '' : ' style="display:none"'); ?>>
		<?php echo isset($_SESSION['ERRORS']['invaliddueversion']) ? '<span class="errorinput" style="display:block;">'.eL('invaliddueversion').'</span>' : ''; ?>
		<label for="dueversion"><?= eL('dueinversion') ?></label>
		<?php echo tpl_select($dueversionselect); ?>
	</li>
	<!-- Due Date -->
	<li<?php echo (in_array('duedate', $fields) && $user->perms('modify_all_tasks')) ? '' : ' style="display:none"'; ?>>
		<label for="due_date"><?= eL('duedate') ?></label>
		<?php echo tpl_datepicker('due_date', '', Req::val('due_date', $task_details['due_date'])); ?>
	</li>
	<!-- Private -->
	<?php if ($user->can_change_private($task_details)): ?>
	<li<?php echo in_array('private', $fields) ? '' : ' style="display:none"'; ?>>
		<label for="private"><?= eL('private') ?></label>
		<?php echo tpl_checkbox('mark_private', Req::val('mark_private', $task_details['mark_private']), 'private'); ?>
	</li>
	<?php endif; ?>

	<?php if ($proj->prefs['use_effort_tracking'] && $user->perms('view_estimated_effort')): ?>
	<li>
		<label for="estimated_effort"><?= eL('estimatedeffort') ?></label>
		<input id="estimated_effort" name="estimated_effort" class="text" type="text" size="5" maxlength="10" value="<?php echo Filters::noXSS(effort::secondsToEditString($task_details['estimated_effort'], $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format'])); ?>" />
		<?= eL('hours') ?>
	</li>
	<?php endif; ?>

	<!-- If no currently selected project is not there, push it on there so don't have to change things -->
	<?php
	$id = Req::val('project_id', $proj->id);
	$selected = false;
	foreach ($fs->projects as $value => $label) {
		if ($label[0] == $id) {
			$selected = true;
			break;
		}
	}

	if (! $selected) {
                $title = '---';
		$foo = array( $id, $title, 'project_id' => $id, 'project_title' => $title);
		array_unshift( $fs->projects,  $foo);
	}

	?>

	<!-- If there is only one choice of projects, then don't bother showing it -->
	<li<?php
		# show the targetproject selector if invalid when moving tasks
		echo isset($_SESSION['ERRORS']['invalidtargetproject']) ? ' class="errorinput"' : ''; ?>>
		<?php echo isset($_SESSION['ERRORS']['invalidtargetproject']) ? '<span class="errorinput" style="display:block;">'.eL('invalidtargetproject').'</span>' : ''; ?>
		<label for="project_id"><?= eL('attachedtoproject') ?></label>
		<select name="project_id" id="project_id">
		<?php echo tpl_options($fs->projects, Req::val('project_id', $proj->id)); ?>
		</select>
	</li>
	</ul>
	<div id="fineprint">
		  <?= eL('openedby') ?> <?php echo tpl_userlink($task_details['opened_by']); ?>
		  - <span title="<?php echo formatDate($task_details['date_opened'], true); ?>"><?php echo formatDate($task_details['date_opened'], false); ?></span>
		  <?php if ($task_details['last_edited_by']): ?>
		  <br />
		  <?= eL('editedby') ?>  <?php echo tpl_userlink($task_details['last_edited_by']); ?>
		  - <span title="<?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], true)); ?>"><?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], false)); ?></span>
		  <?php endif; ?>
	</div>
</div>
<div id="taskdetailsfull">
	<label for="itemsummary"<?php echo isset($_SESSION['ERRORS']['summaryrequired']) ? ' class="summary errorinput" title="'.eL('summaryrequired').'"':' class="summary"'; ?>>FS#<?php echo Filters::noXSS($task_details['task_id']); ?> <?php echo Filters::noXSS(L('summary')); ?>:
		<input placeholder="<?= eL('summary') ?>" type="text" name="item_summary" id="itemsummary" maxlength="100" value="<?php echo Filters::noXSS(Req::val('item_summary', $task_details['item_summary'])); ?>" />
	</label>
	<?php if ($proj->prefs['use_tags']): ?>
		<?php
		foreach($tags as $tag): $tagnames[]= Filters::noXSS($tag['tag']); endforeach;
		isset($tagnames) ? $tagstring=implode(';',$tagnames) : $tagstring='';
		?>
		<input type="checkbox" id="availtags">
		<div>
			<label for="tags" title="<?= eL('tagsinfo') ?>"><?= eL('tags') ?>:</label>
			<input title="<?= eL('tagsinfo') ?>" placeholder="<?= eL('tags') ?>" type="text" name="tags" id="tags" maxlength="200" value="<?php echo Filters::noXSS(Req::val('tags', $tagstring)); ?>" />
			<label for="availtags" class="button" id="availtagsshow"><i class="fa fa-plus"></i></label>
			<label for="availtags" class="button" id="availtagshide"><i class="fa fa-minus"></i></label>
		</div>
		<div id="tagrender"></div>
		<fieldset id="availtaglist">
                <legend><?= eL('tagsavail') ?></legend>
                <?php
                foreach ($taglist as $tagavail) {
                        echo tpl_tag($tagavail['tag_id']); 
                } ?>
                </fieldset>
	<?php endif; ?>
	<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
		<div class="hide preview" id="preview"></div>
		<button tabindex="9" type="button" onclick="showPreview('details', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
	<?php endif; ?>
	<?php echo TextFormatter::textarea('detailed_desc', 15, 70, array('id' => 'details'), Req::val('detailed_desc', $task_details['detailed_desc'])); ?>
	<br />
	<?php
	/* Our CKEditor 4.16 setup has undo/redo plugin and the reset button in this template has no functionality if javascript is enabled */
	if ($conf['general']['syntax_plugin'] == 'html'): ?>
		<noscript><button type="reset"><?= eL('reset') ?></button></noscript>
	<?php else: ?>
		<button type="reset"><?= eL('reset') ?></button>
	<?php endif; ?>
	<br />

	<div id="addlinkbox">
	<?php
	$links = $proj->listTaskLinks($task_details['task_id']);
	$this->display('common.editlinks.tpl', 'links', $links); ?>
	<?php if ($user->perms('create_attachments')): ?>
		<input id="link1" tabindex="8" class="text" type="text" maxlength="150" name="userlink[]" />
		<script>
		// hide the fallback input field if javascript is enabled
		document.getElementById("link1").style.display='none';
		</script>
		<button id="addlinkbox_addalink" tabindex="10" type="button" onclick="addLinkField('addlinkbox')"><?= eL('addalink') ?></button>
		<button id="addlinkbox_addanotherlink" tabindex="10" style="display: none" type="button" onclick="addLinkField('addlinkbox')"><?= eL('addalink') ?></button>
		<br />
		<span style="display: none"><?php /* this span is shown/copied by javascript when adding links */ ?>
			<input tabindex="8" class="text" type="text" maxlength="150" name="userlink[]" />
			<a href="javascript://" tabindex="9" class="button fa fa-remove fa-lg" title="<?= eL('remove') ?>" onclick="removeLinkField(this, 'addlinkbox');"></a><br />
		</span>
	<?php endif; ?>
	</div>
	<div id="uploadfilebox">
	<?php 
	$attachments = $proj->listTaskAttachments($task_details['task_id']);
	$this->display('common.editattachments.tpl', 'attachments', $attachments);
	if ($user->perms('create_attachments')): ?>
		<input id="file1" tabindex="5" class="file" type="file" size="55" name="usertaskfile[]" />
		<script>
		// hide the fallback input field if javascript is enabled
		document.getElementById("file1").style.display='none';
		</script>
		<button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields()">
			<?= eL('uploadafile') ?> (<?= eL('max') ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?= eL('MiB') ?>)
		</button>
		<button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields()">
			<?= eL('attachanotherfile') ?> (<?= eL('max') ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?= eL('MiB') ?>)
		</button>
		<br />
		<span style="display: none"><?php /* this span is shown/copied by javascript when adding files */ ?>
			<input tabindex="5" class="file" type="file" size="55" name="usertaskfile[]" />
			<a href="javascript://" tabindex="6" class="button fa fa-remove fa-lg" title="<?= eL('remove') ?>" onclick="removeUploadField(this);"></a><br />
		</span>
	<?php endif; ?>
	</div>
	<div class="buttons">
		<?php if ($user->perms('add_comments') && (!$task_details['is_closed'] || $proj->prefs['comment_closed'])): ?>
		<input type="checkbox" id="s_addcomment" />
		<label for="s_addcomment" title="<?= eL('addcomment') ?>">
		<span class="fa-stack">
		<i class="fa fa-comment-o fa-stack-2x"></i>
		<i class="fa fa-plus fa-stack-1x"></i>
		</span>
		</label>
		<div id="edit_add_comment">
		<label for="comment_text"><?php echo Filters::noXSS(L('comment')); ?></label>
		<textarea accesskey="r" tabindex="8" id="comment_text" name="comment_text" cols="50" rows="2"></textarea>
		</div>
		<br />
		<?php endif; ?>
		<button type="submit" class="positive" accesskey="s" onclick="return checkok('<?php echo Filters::noJsXSS($baseurl); ?>js/callbacks/checksave.php?time=<?php echo Filters::noXSS(time()); ?>&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>', '<?php echo Filters::noJsXSS(L('alreadyedited')); ?>', 'taskeditform')"><?php echo Filters::noXSS(L('savedetails')); ?></button>
		<a class="button" href="<?php echo Filters::noXSS(createUrl('details', $task_details['task_id'])); ?>"><?= eL('canceledit') ?></a>
	</div>
</div>
<div class="clear"></div>
</div>
</form>
