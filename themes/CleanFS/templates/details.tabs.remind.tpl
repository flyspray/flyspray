<?php if (!$task_details['is_closed']): ?>
<div id="remind" class="tab">
	<?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#remind'); ?>
<?php if (count($reminders)): ?>
	<table id="taskreminders">
	<thead>
	<tr>
		<th class="ttcolumn">
			<a class="toggle_selected" title="<?php echo Filters::noXSS(L('toggleselected')); ?>" href="javascript:ToggleSelected('taskreminders')"><span class="fas fa-exchange"></span></a>
		</th>
		<th class="user"><?php echo Filters::noXSS(L('user')); ?></th>
		<th class="startdate"><?php echo Filters::noXSS(L('startat')); ?></th>
		<th class="frequency"><?php echo Filters::noXSS(L('frequency')); ?></th>
		<th class="message"><?php echo Filters::noXSS(L('message')); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($reminders as $row): ?>
	<tr>
		<td class="reminder_ttcolumn">
			<input type="checkbox" name="reminder_id[]" <?php echo tpl_disableif(!$user->can_edit_task($task_details)); ?> value="<?php echo Filters::noXSS($row['reminder_id']); ?>" />
		</td>
		<td class="reminder_user"><?php echo tpl_userlink($row['user_id']); ?></td>
		<td class="reminder_startdate"><?php echo Filters::noXSS(formatDate($row['start_time'])); ?></td>
<?php
// Work out the unit of time to display
if ($row['how_often'] < 86400) {
	$how_often = $row['how_often'] / 3600 . ' ' . L('hours');
} elseif ($row['how_often'] < 604800) {
	$how_often = $row['how_often'] / 86400 . ' ' . L('days');
} else {
	$how_often = $row['how_often'] / 604800 . ' ' . L('weeks');
}
?>
		<td class="reminder_frequency"><?php echo Filters::noXSS($how_often); ?></td>
		<td class="reminder_message"><?php echo TextFormatter::render($row['reminder_message']); ?></td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<div class="buttons">
		<input type="hidden" name="action" value="deletereminder" />
		<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
		<button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button>
	</div>
<?php else: ?>
	<p><?php echo Filters::noXSS(L('tasknoreminders')); ?></p>
<?php endif; ?>
	</form>

	<fieldset>
		<legend><?php echo Filters::noXSS(L('addreminder')); ?></legend>

		<?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#remind',null,null,null,'id="formaddreminder"'); ?>
		<ul class="form_elements">
			<li>
				<label class="default multisel" for="to_user_id"><?php echo Filters::noXSS(L('remindthisuser')); ?></label>
				<div class="valuewrap">
					<input type="hidden" name="action" value="details.addreminder" />
					<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
					<?php echo tpl_userselect('to_user_id', Req::val('to_user_id'), 'to_user_id'); ?>
				</div>
			</li>
			<li>
				<label for="timeamount1"><?php echo Filters::noXSS(L('thisoften')); ?></label>
				<div class="valuewrap">
					<div class="valuemulti">
						<input class="text fi-xx-small" type="text" value="<?php echo Filters::noXSS(Req::val('timeamount1')); ?>" id="timeamount1" name="timeamount1" size="3" maxlength="3" />
						<select class="adminlist" name="timetype1">
						<?php echo tpl_options(array(3600 => L('hours'), 86400 => L('days'), 604800 => L('weeks')), Req::val('timetype1')); ?>
						</select>
					</div>
				</div>
			</li>
			<li>
				<label for="timeamount2"><?php echo Filters::noXSS(L('startat')); ?></label>
				<div class="valuewrap">
					<?php echo tpl_datepicker('timeamount2', '', Req::val('timeamount2', formatDate(time()))); ?>
				</div>
			</li>
			<li class="wide-element">
				<label for="reminder_message"><?php echo Filters::noXSS(L('message')); ?></label>
				<div class="valuewrap">
					<textarea class="text txta-medium" id="reminder_message" name="reminder_message" rows="10" cols="72"><?php echo Filters::noXSS(Req::val('reminder_message', L('defaultreminder') . "\n\n" . CreateURL('details', $task_details['task_id']))); ?></textarea>
				</div>
			</li>
		</ul>

		<div class="buttons">
			<button type="submit"><?php echo Filters::noXSS(L('addreminder')); ?></button>
		</div>
		</form>
	</fieldset>
</div>
<?php endif; ?>
