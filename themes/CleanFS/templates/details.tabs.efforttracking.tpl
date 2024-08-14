<div id="effort" class="tab">
<?php echo tpl_form(Filters::noXSS(createUrl('details', $task_details['task_id'])).'#effort'); ?>
<?php if ($user->perms('track_effort')) { ?>
	<?php if ($effort->countActiveTracking(true) == 0): ?>
	<div class="buttons">
		<button type="submit" name="start_tracking" value="true"><span class="fas fa-stopwatch"></span> <?= eL('starteffort') ?></button>
	</div>
	<?php endif; ?>

	<fieldset>
		<legend><?= eL('manualeffort') ?></legend>
		<ul class="form_elements">
			<li>
				<label for="effort_to_add"><?= eL('effortamount') ?></label>
				<div class="valuewrap">
					<input id="effort_to_add" name="effort_to_add" class="text fi-x-small ta-e" type="text" size="5" maxlength="100" value="00:00" title="hh:mm"/>
				</div>
			</li>
			<li>
				<label for="effort_description"><?= eL('description') ?></label>
				<div class="valuewrap">
					<input type="text" name="effort_description" class="fi-x-large" size="10" />
					<span class="note"><?= eL('noteoptional') ?></span>
				</div>
			</li>
		</ul>

		<div class="buttons">
			<input type="hidden" name="action" value="details.efforttracking"/>
			<button type="submit" name="manual_effort" value="true"><?= eL('addeffort') ?></button>
		</div>
	</fieldset>
<?php } ?>

	<table id="taskefforthistory">
	<thead>
	<tr>
		<th class="date"><?= eL('date') ?></th>
		<th class="user"><?= eL('user') ?></th>
		<th class="effort"><?= eL('effort') ?> (H:M)</th>
		<th class="description"><?= eL('description') ?></th>
		<th class="actions"></th>
	</tr>
	</thead>
	<tbody>
<?php foreach($effort->details as $details): ?>
	<tr<?= (is_null($details['end_timestamp']) ? ' class="active-effort"' : '') ?>>
		<td class="effort_date"><?php echo Filters::noXSS(formatDate($details['date_added'], true)); ?></td>
		<td class="effort_user"><?php echo tpl_userlink($details['user_id']); ?></td>
		<td class="effort_effort">
		<?php if($details['effort'] == 0 && $details['end_timestamp']==false): ?>
			<span class="fas fa-stopwatch fa-lg" title="<?= eL('trackinginprogress') ?>"></span> (<?php
				// $details['start_timestamp'] = time();

				echo effort::secondsToString(
						time()-$details['start_timestamp'],
						$proj->prefs['hours_per_manday'],
						$proj->prefs['current_effort_done_format']
				 );
			?>)
		<?php else:
			echo effort::secondsToString($details['effort'], $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
		endif; ?>
		</td>
		<td class="effort_description">
			<?= Filters::noXSS($details['description']) ?>
		</td>
		<td class="effort_actions"><?php /* TODO: make this never empty */ ?>
			<?php if($user->id == $details['user_id'] && is_null($details['end_timestamp'])): ?>
			<button type="submit" name="stop_tracking" value="true"><span class="fas fa-circle-stop"></span> <?= eL('endeffort') ?></button>
			<button type="submit" name="cancel_tracking" value="true"><span class="fas fa-trash-can"></span> <?= eL('cleareffort') ?></button>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	</form>
	<!-- a href with target flyspraytimers for webbrowsers with disabled javascript -->
	<a href="/mytimers.php" onclick="javascript:window.open('/mytimers.php','flyspraytimers','innerWidth=320,innerHeight=300,scrollbars=no');return false;" target="flyspraytimers">watch my effort tracking timers</a>
</div>
