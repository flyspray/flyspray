<?php echo tpl_form(Filters::noXSS(CreateUrl($do,'newgroup',$proj->id)),null,null,null,'id="newgroup"'); ?>
<ul class="form_elements">
	<li class="required">
		<label for="groupname"><?php echo Filters::noXSS(L('groupname')); ?></label>
		<div class="valuewrap">
			<input id="groupname" class="required text fi-large" type="text" value="<?php echo Filters::noXSS(Req::val('group_name')); ?>" name="group_name" size="20" maxlength="20" />
		</div>
	</li>
	<li>
		<label for="groupdesc"><?php echo Filters::noXSS(L('description')); ?></label>
		<div class="valuewrap">
			<input id="groupdesc" class="text fi-xx-large" type="text" value="<?php echo Filters::noXSS(Req::val('group_desc')); ?>" name="group_desc" size="50" maxlength="100" />
		</div>
	</li>
</ul>

<fieldset>
	<legend><?php echo Filters::noXSS(L('permissions')); ?></legend>

	<div class="checks_wrap">
		<ul class="form_elements checks_list">
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('manage_project', Req::val('manage_project'), 'manageproject'); ?>
				</div>
				<label for="manageproject"><?php echo Filters::noXSS(L('projectmanager')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_reports', Req::val('view_reports'), 'viewreports'); ?>
				</div>
				<label for="viewreports"><?php echo Filters::noXSS(L('viewreports')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_roadmap', Req::val('view_roadmap', !Req::val('action')), 'canviewroadmap'); ?>
				</div>
				<label for="canviewroadmap"><?php echo Filters::noXSS(L('canviewroadmap')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_history', Req::val('view_history'), 'viewhistory'); ?>
				</div>
				<label for="viewhistory"><?php echo Filters::noXSS(L('viewhistory')); ?></label>
			</li>
<?php if (!$proj->id): ?>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('group_open', Req::val('group_open'), 'groupopen'); ?>
				</div>
				<label for="groupopen"><?php echo Filters::noXSS(L('groupenabled')); ?></label>
			</li>
<?php endif; ?>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_estimated_effort', Req::val('view_estimated_effort'), 'viewestimatedeffort'); ?>
				</div>
				<label for="viewestimatedeffort"><?php echo Filters::noXSS(L('viewestimatedeffort')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_current_effort_done', Req::val('view_current_effort_done'), 'viewcurrenteffortdone'); ?>
				</div>
				<label for="viewcurrenteffortdone"><?php echo Filters::noXSS(L('viewcurrenteffortdone')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('track_effort', Req::val('track_effort'), 'trackeffort'); ?>
				</div>
				<label for="trackeffort"><?php echo Filters::noXSS(L('trackeffort')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('add_votes', Req::val('add_votes'), 'canvote'); ?>
				</div>
				<label for="canvote"><?php echo Filters::noXSS(L('canvote')); ?></label>
			</li>
		</ul>

		<ul class="form_elements checks_list">
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_tasks', Req::val('view_tasks'), 'viewtasks'); ?>
				</div>
				<label for="viewtasks"><?php echo Filters::noXSS(L('viewtasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_own_tasks', Req::val('view_own_tasks'), 'viewowntasks'); ?>
				</div>
				<label for="viewowntasks"><?php echo Filters::noXSS(L('viewowntasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_groups_tasks', Req::val('view_groups_tasks'), 'viewgroupstasks'); ?>
				</div>
				<label for="viewgroupstasks"><?php echo Filters::noXSS(L('viewgroupstasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('open_new_tasks', Req::val('open_new_tasks'), 'opennewtasks'); ?>
				</div>
				<label for="opennewtasks"><?php echo Filters::noXSS(L('opennewtasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('add_multiple_tasks', Req::val('add_multiple_tasks'), 'canopenmultipletasks'); ?>
				</div>
				<label for="canopenmultipletasks"><?php echo Filters::noXSS(L('addmultipletasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks'), 'modifyowntasks'); ?>
				</div>
				<label for="modifyowntasks"><?php echo Filters::noXSS(L('modifyowntasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks'), 'modifyalltasks'); ?>
				</div>
				<label for="modifyalltasks"><?php echo Filters::noXSS(L('modifyalltasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('close_own_tasks', Req::val('close_own_tasks'), 'closeowntasks'); ?>
				</div>
				<label for="closeowntasks"><?php echo Filters::noXSS(L('closeowntasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('close_other_tasks', Req::val('close_other_tasks'), 'closeothertasks'); ?>
				</div>
				<label for="closeothertasks"><?php echo Filters::noXSS(L('closeothertasks')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('assign_to_self', Req::val('assign_to_self'), 'assigntoself'); ?>
				</div>
				<label for="assigntoself"><?php echo Filters::noXSS(L('assigntoself')); ?></label>
			</li>
		</ul>

		<ul class="form_elements checks_list">
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self'), 'assignotherstoself'); ?>
				</div>
				<label for="assignotherstoself"><?php echo Filters::noXSS(L('assignotherstoself')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('add_to_assignees', Req::val('add_to_assignees'), 'addtoassignees'); ?>
				</div>
				<label for="addtoassignees"><?php echo Filters::noXSS(L('addtoassignees')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('edit_assignments', Req::val('edit_assignments'), 'editassignments'); ?>
				</div>
				<label for="editassignments"><?php echo Filters::noXSS(L('editassignments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('show_as_assignees', Req::val('show_as_assignees'), 'show_as_assignees'); ?>
				</div>
				<label for="show_as_assignees"><?php echo Filters::noXSS(L('showasassignees')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('view_comments', Req::val('view_comments'), 'viewcomments'); ?>
				</div>
				<label for="viewcomments"><?php echo Filters::noXSS(L('viewcomments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('add_comments', Req::val('add_comments'), 'addcomments'); ?>
				</div>
				<label for="addcomments"><?php echo Filters::noXSS(L('addcomments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('edit_own_comments', Req::val('edit_own_comments'), 'editowncomments'); ?>
				</div>
				<label for="editowncomments"><?php echo Filters::noXSS(L('editowncomments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('edit_comments', Req::val('edit_comments'), 'editcomments'); ?>
				</div>
				<label for="editcomments"><?php echo Filters::noXSS(L('editcomments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('delete_comments', Req::val('delete_comments'), 'deletecomments'); ?>
				</div>
				<label for="deletecomments"><?php echo Filters::noXSS(L('deletecomments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('create_attachments', Req::val('create_attachments'), 'createattachments'); ?>
				</div>
				<label for="createattachments"><?php echo Filters::noXSS(L('createattachments')); ?></label>
			</li>
			<li>
				<div class="valuewrap">
					<?php echo tpl_checkbox('delete_attachments', Req::val('delete_attachments'), 'deleteattachments'); ?>
				</div>
				<label for="deleteattachments"><?php echo Filters::noXSS(L('deleteattachments')); ?></label>
			</li>
		</ul>
	</div>
</fieldset>

<div class="buttons">
	<input type="hidden" name="action" value="<?php if ($proj->id): ?>pm<?php else: ?>admin<?php endif; ?>.newgroup" />
	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS(Req::val('project')); ?>" />
	<button type="submit" class="positive"><?php echo Filters::noXSS(L('addthisgroup')); ?></button>
</div>
</form>
