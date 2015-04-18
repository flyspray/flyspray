<div id="notify" class="tab">
	<p><em><?php echo Filters::noXSS(L('theseusersnotify')); ?></em></p>
	<?php foreach ($notifications as $row): ?>
		<div>
			<?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#notify',null,null,null,'style="display:inline"'); ?>
			<input type="hidden" name="action" value="remove_notification" />
			<input type="hidden" name="ids" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
			<input type="hidden" name="user_id" value="<?php echo Filters::noXSS($row['user_id']); ?>" />
			<button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button>
			<?php echo tpl_userlink($row['user_id']); ?>
			</form>
			<!--
			<a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=remove_notification&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($row['user_id']); ?>#notify"><?php echo Filters::noXSS(L('remove')); ?></a>
			-->
		</div>
	<?php endforeach; ?>

	<?php if ($user->perms('manage_project')): ?>
		<?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#notify'); ?>
		<div>
			<label class="default multisel" for="notif_user_id"><?php echo Filters::noXSS(L('addusertolist')); ?>: </label>
			<?php echo tpl_userselect('user_name', Req::val('user_name'), 'notif_user_id'); ?>
			<button type="submit"><?php echo Filters::noXSS(L('add')); ?></button>
			<input type="hidden" name="ids" value="<?php echo Filters::noXSS(Req::num('ids', $task_details['task_id'])); ?>" />
			<input type="hidden" name="action" value="details.add_notification" />
		</div>
		</form>
	<?php endif; ?>
</div>

