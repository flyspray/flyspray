<div id="related" class="tab">
	<div class="relatedwrap">
		<div class="related">
			<?php echo tpl_form(Filters::noXSS(createURL('details', $task_details['task_id'])).'#related');?>
			<table id="tasks_related" class="userlist">
			<thead>
			<tr>
				<th>
					<a class="toggle_selected" href="javascript:ToggleSelected('tasks_related')">
<?php /* <img title="<?php echo Filters::noXSS(L('toggleselected')); ?>" alt="<?php echo Filters::noXSS(L('toggleselected')); ?>" src="<?php echo Filters::noXSS($this->get_image('kaboodleloop')); ?>" width="16" height="16" /> */ ?>
					</a>
				</th>
				<th class="text">
					<?php echo Filters::noXSS(L('tasksrelated')); ?> (<?php echo Filters::noXSS(count($related)); ?>)
				</th>
			</tr>
			</thead>
			<tbody>
<?php foreach ($related as $row): ?>
			<tr>
				<td class="ttcolumn">
					<input type="checkbox" name="related_id[]" <?php echo tpl_disableif(!$user->can_edit_task($task_details)); ?> value="<?php echo Filters::noXSS($row['related_id']); ?>" />
				</td>
				<td>
					<?php echo tpl_tasklink($row); ?>
				</td>
			</tr>
<?php endforeach; ?>
			</tbody>
			</table>

			<div class="buttons">
				<input type="hidden" name="action" value="remove_related" />
				<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
				<button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button>
			</div>


			</form>
		</div>
		<div class="related">
			<table id="duplicate_tasks" class="userlist">
			<thead>
			<tr>
				<th>
					<?php echo Filters::noXSS(L('duplicatetasks')); ?> (<?php echo Filters::noXSS(count($duplicates)); ?>)
				</th>
			</tr>
			</thead>
			<tbody>
<?php foreach ($duplicates as $row): ?>
			<tr>
				<td>
					<?php echo tpl_tasklink($row); ?>
				</td>
			</tr>
<?php endforeach; ?>
			</tbody>
			</table>
		</div>
	</div>

<?php if ($user->can_edit_task($task_details) && !$task_details['is_closed']): ?>
	<?php echo tpl_form(Filters::noXSS(createURL('details', $task_details['task_id'])).'#related',null,null,null,'class="clear" id="formaddrelatedtask"'); ?>
	<ul class="form_elements">
		<li>
			<label for="related_task_input"><?php echo Filters::noXSS(L('addnewrelated')); ?> </label>
			<div class="valuewrap">
				<div class="valuemulti">
					FS# <input name="related_task" id="related_task_input" type="text" class="text fi-x-small" size="10" maxlength="10" />
					<input type="hidden" name="action" value="details.add_related" />
					<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
					<button type="submit" onclick="return checkok('<?php echo Filters::noJsXSS($baseurl); ?>js/callbacks/checkrelated.php?related_task=' + $('related_task_input').value + '&amp;project=<?php echo Filters::noXSS($proj->id); ?>', '<?php echo Filters::noJsXSS(L('relatedproject')); ?>', 'formaddrelatedtask')"><?php echo Filters::noXSS(L('add')); ?></button>
				</div>
			</div>
		</li>
	</ul>
</form>
<?php endif; ?>
</div>

