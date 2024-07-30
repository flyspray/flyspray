<form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get" id="groupselect">
<ul class="form_elements">
	<li>
		<label for="selectgroup" class="minimal"><?php echo Filters::noXSS(L('editgroup')); ?></label>
		<div class="valuewrap">
			<div class="valuemulti">
				<select name="id" id="selectgroup">
					<?php echo tpl_options(Flyspray::ListGroups($proj->id), Req::num('id')); ?>
				</select>
				<div class="valuemultipair">
					<button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
					<input type="hidden" name="do" value="<?php echo Filters::noXSS(Req::val('do')); ?>" />
					<input type="hidden" name="area" value="editgroup" />
				</div>
			</div>
		</div>
	</li>
</ul>
</form>

<?php echo tpl_form(Filters::noXSS(CreateURL('editgroup', Req::num('id'), $do)), null, null, null, 'id="editgroup"'); ?>
<fieldset>
<legend><?php echo Filters::noXSS(L('editgroup')); ?> <?php echo Filters::noXSS(Req::val('group_name', $group_details['group_name'])); ?></legend>

<ul class="form_elements">
	<li>
		<label for="groupname"><?php echo Filters::noXSS(L('groupname')); ?></label>
		<div class="valuewrap">
			<input id="groupname" class="text fi-large" type="text" name="group_name" size="20" maxlength="20" value="<?php echo Filters::noXSS(Req::val('group_name', $group_details['group_name'])); ?>" />
		</div>
	</li>
	<li>
		<label for="groupdesc"><?php echo Filters::noXSS(L('description')); ?></label>
		<div class="valuewrap">
			<input id="groupdesc" class="text fi-xx-large" type="text" name="group_desc" size="50" maxlength="100" value="<?php echo Filters::noXSS(Req::val('group_desc', $group_details['group_desc'])); ?>" />
		</div>
	</li>
</ul>

<ul class="form_elements">
	<?php if ($group_details['group_id'] != '1'): ?>
	<li>
		<label for="delete_group"><?php echo Filters::noXSS(L('deletegroup')); ?></label>
		<div class="valuewrap">
			<div class="valuemulti">
			<input type="checkbox" id="delete_group" name="delete_group" />
			<div class="valuemulti">
			<div class="valuemultipair">
				<select name="move_to">
					<?php echo tpl_options( array_merge( ($proj->id) ? array(L('nogroup')) : array(), Flyspray::listGroups($proj->id)), null, false, null, $group_details['group_id']); ?>
				</select>
				</div>
			</div>
		</div>
	</li>
	<?php endif; ?>
	<li>
		<label for="add_user"><span class="fas fa-user-plus"></span> <?php echo Filters::noXSS(L('addusergroup')); ?></label>
		<div class="valuewrap">
			<?php echo tpl_userselect('uid', '', 'add_user'); ?>
		</div>
	</li>
</ul>

</fieldset>

<fieldset>
	<legend><?php echo Filters::noXSS(L('editpermissions')); ?></legend>
	<?php if ($group_details['group_id'] == 1): ?>
	<div class="box">
		<span class="fas fa-circle-exclamation fa-lg" style="margin-right: .35em;"></span><?php echo Filters::noXSS(L('notshownforadmin')); ?>
	</div>
	<?php else: ?>
<div class="checks_wrap">
<ul class="form_elements checks_list">
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('manage_project', Req::val('manage_project', !Req::val('action') && $group_details['manage_project']), 'projectmanager'); ?>
		</div>
		<label for="projectmanager"><?php echo Filters::noXSS(L('projectmanager')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_reports', Req::val('view_reports', !Req::val('action') && $group_details['view_reports']), 'viewreports'); ?>
		</div>
		<label for="viewreports"><?php echo Filters::noXSS(L('viewreports')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_roadmap', Req::val('view_roadmap', !Req::val('action') && $group_details['view_roadmap']), 'canviewroadmap'); ?>
		</div>
		<label for="canviewroadmap"><?php echo Filters::noXSS(L('canviewroadmap')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_history', Req::val('view_history', !Req::val('action') && $group_details['view_history']), 'viewhistory'); ?>
		</div>
		<label for="viewhistory"><?php echo Filters::noXSS(L('viewhistory')); ?></label>
	</li>
	<?php if (!$proj->id): ?>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('group_open', Req::val('group_open', !Req::val('action') && $group_details['group_open']), 'groupopen'); ?>
		</div>
		<label for="groupopen"><?php echo Filters::noXSS(L('groupenabled')); ?></label>
	</li>
	<?php endif; ?>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_estimated_effort', Req::val('view_estimated_effort', !Req::val('action') && $group_details['view_estimated_effort']), 'viewestimatedeffort'); ?>
		</div>
		<label for="viewestimatedeffort"><?php echo Filters::noXSS(L('viewestimatedeffort')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_current_effort_done', Req::val('view_current_effort_done', !Req::val('action') && $group_details['view_current_effort_done']), 'viewactualeffort'); ?>
		</div>
		<label for="viewcurrenteffortdone"><?php echo Filters::noXSS(L('viewcurrenteffortdone')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('track_effort', Req::val('track_effort', !Req::val('action') && $group_details['track_effort']), 'trackeffort'); ?>
		</div>
		<label for="trackeffort"><?php echo Filters::noXSS(L('trackeffort')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('add_votes', Req::val('add_votes', !Req::val('action') && $group_details['add_votes']), 'canvote'); ?>
		</div>
		<label for="canvote"><?php echo Filters::noXSS(L('canvote')); ?></label>
	</li>
</ul>
<ul class="form_elements checks_list">
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_tasks', Req::val('view_tasks', !Req::val('action') && $group_details['view_tasks']), 'viewtasks'); ?>
		</div>
		<label for="viewtasks"><?php echo Filters::noXSS(L('viewtasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_own_tasks', Req::val('view_own_tasks', !Req::val('action') && $group_details['view_own_tasks']), 'viewowntasks'); ?>
		</div>
		<label for="viewowntasks"><?php echo Filters::noXSS(L('viewowntasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_groups_tasks', Req::val('view_groups_tasks', !Req::val('action') && $group_details['view_groups_tasks']), 'viewgroupstasks'); ?>
		</div>
		<label for="viewgroupstasks"><?php echo Filters::noXSS(L('viewgroupstasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', !Req::val('action') && $group_details['open_new_tasks']), 'canopenjobs'); ?>
		</div>
		<label for="canopenjobs"><?php echo Filters::noXSS(L('opennewtasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('add_multiple_tasks', Req::val('add_multiple_tasks', !Req::val('action') && $group_details['add_multiple_tasks']), 'canopenmultipletasks'); ?>
		</div>
		<label for="canopenmultipletasks"><?php echo Filters::noXSS(L('addmultipletasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks', !Req::val('action') && $group_details['modify_own_tasks']), 'modifyowntasks'); ?>
		</div>
		<label for="modifyowntasks"><?php echo Filters::noXSS(L('modifyowntasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks', !Req::val('action') && $group_details['modify_all_tasks']), 'modifyalltasks'); ?>
		</div>
		<label for="modifyalltasks"><?php echo Filters::noXSS(L('modifyalltasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('close_own_tasks', Req::val('close_own_tasks', !Req::val('action') && $group_details['close_own_tasks']), 'closeowntasks'); ?>
		</div>
		<label for="closeowntasks"><?php echo Filters::noXSS(L('closeowntasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('close_other_tasks', Req::val('close_other_tasks', !Req::val('action') && $group_details['close_other_tasks']), 'closeothertasks'); ?>
		</div>
		<label for="closeothertasks"><?php echo Filters::noXSS(L('closeothertasks')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('assign_to_self', Req::val('assign_to_self', !Req::val('action') && $group_details['assign_to_self']), 'assigntoself'); ?>
		</div>
		<label for="assigntoself"><?php echo Filters::noXSS(L('assigntoself')); ?></label>
	</li>
</ul>
<ul class="form_elements checks_list">
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self', !Req::val('action') && $group_details['assign_others_to_self']), 'assignotherstoself'); ?>
		</div>
		<label for="assignotherstoself"><?php echo Filters::noXSS(L('assignotherstoself')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('add_to_assignees', Req::val('add_to_assignees', !Req::val('action') && $group_details['add_to_assignees']), 'addtoassignees'); ?>
		</div>
		<label for="addtoassignees"><?php echo Filters::noXSS(L('addtoassignees')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('edit_assignments', Req::val('edit_assignments', !Req::val('action') && $group_details['edit_assignments']), 'editassignments'); ?>
		</div>
		<label for="editassignments"><?php echo Filters::noXSS(L('editassignments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('show_as_assignees', Req::val('show_as_assignees', !Req::val('action') && $group_details['show_as_assignees']), 'show_as_assignees'); ?>
		</div>
		<label for="show_as_assignees"><?php echo Filters::noXSS(L('showasassignees')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('view_comments', Req::val('view_comments', !Req::val('action') && $group_details['view_comments']), 'viewcomments'); ?>
		</div>
		<label for="viewcomments"><?php echo Filters::noXSS(L('viewcomments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('add_comments', Req::val('add_comments', !Req::val('action') && $group_details['add_comments']), 'canaddcomments'); ?>
		</div>
		<label for="canaddcomments"><?php echo Filters::noXSS(L('addcomments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('edit_own_comments', Req::val('edit_own_comments', !Req::val('action') && $group_details['edit_own_comments']), 'editowncomments'); ?>
		</div>
		<label for="editowncomments"><?php echo Filters::noXSS(L('editowncomments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('edit_comments', Req::val('edit_comments', !Req::val('action') && $group_details['edit_comments']), 'editcomments'); ?>
		</div>
		<label for="editcomments"><?php echo Filters::noXSS(L('editcomments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('delete_comments', Req::val('delete_comments', !Req::val('action') && $group_details['delete_comments']), 'deletecomments'); ?>
		</div>
		<label for="deletecomments"><?php echo Filters::noXSS(L('deletecomments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('create_attachments', Req::val('create_attachments', !Req::val('action') && $group_details['create_attachments']), 'createattachments'); ?>
		</div>
		<label for="createattachments"><?php echo Filters::noXSS(L('createattachments')); ?></label>
	</li>
	<li>
		<div class="valuewrap">
			<?php echo tpl_checkbox('delete_attachments', Req::val('delete_attachments', !Req::val('action') && $group_details['delete_attachments']), 'deleteattachments'); ?>
		</div>
		<label for="deleteattachments"><?php echo Filters::noXSS(L('deleteattachments')); ?></label>
	</li>
</ul>
</div>
	<?php endif; ?>
</fieldset>

<div class="buttons">
	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<input type="hidden" name="action" value="<?php echo Filters::noXSS(Req::val('action', $do.'.editgroup')); ?>" />
	<input type="hidden" name="area" value="editgroup" />
	<input type="hidden" name="group_id" value="<?php echo Filters::noXSS($group_details['group_id']); ?>" />
	<button type="submit" class="positive"><?php echo Filters::noXSS(L('updatedetails')); ?></button>
</div>
</form>

<h3 style="margin-top: 2em;"><?php echo Filters::noXSS(L('groupmembers')); ?></h3>
<?php echo tpl_form(Filters::noXSS(CreateURL('editgroup', Req::num('id'), $do)), null, null, null, 'id="userlist"'); ?>
<table id="manage_users_in_groups" class="userlist">
<thead>
<tr>
	<th>
		<a href="javascript:ToggleSelected('manage_users_in_groups')" title="<?php echo Filters::noXSS(L('toggleselected')); ?>">
			<span class="fas fa-repeat fa-lg"></span>
		</a>
	</th>
	<th class="text"><?php echo Filters::noXSS(L('username')); ?></th>
	<th class="text"><?php echo Filters::noXSS(L('realname')); ?></th>
	<th><?php echo Filters::noXSS(L('accountenabled')); ?></th>
</tr>
</thead>
<tbody>
<?php
	foreach(Project::listUsersIn($group_details['group_id']) as $usr):
?>
<tr>
	<td class="ttcolumn"><?php echo tpl_checkbox('users['.$usr['user_id'].']'); ?></td>
	<td><a href="<?php echo Filters::noXSS(CreateURL('edituser', $usr['user_id'])); ?>"><?php echo Filters::noXSS($usr['user_name']); ?></a></td>
	<td><?php echo Filters::noXSS($usr['real_name']); ?></td>
	<?php if ($usr['account_enabled']) : ?>
	<td class="imgcol"><span class="fas fa-check" style="color:#090" title="<?php echo L('yes'); ?>"></span></td>
	<?php else: ?>
	<td class="imgcol"><span class="fas fa-ban" style="color:#900" title="<?php echo L('no'); ?>"></span></td>
	<?php endif; ?>
</tr>
<?php
	$users_in[] = $usr['user_id'];
	endforeach;
?>
</tbody>
</table>

<div class="buttons">
	<button type="submit"><?php echo Filters::noXSS(L('moveuserstogroup')); ?></button>
	<select class="adminlist" name="switch_to_group">
	<?php if ($proj->id): ?>
	<option value="0"><?php echo Filters::noXSS(L('nogroup')); ?></option>
	<?php endif; ?>
	<?php echo tpl_options(Flyspray::listGroups($proj->id), null, false, null, $group_details['group_id']); ?>
	</select>

	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<input type="hidden" name="action" value="movetogroup" />
	<input type="hidden" name="old_group" value="<?php echo Filters::noXSS($group_details['group_id']); ?>" />
</div>
</form>
