<div id="effort" class="tab">
	<?php echo tpl_form(Filters::noXSS(createUrl('details', $task_details['task_id'])).'#effort'); ?>
	<?php if ($user->perms('track_effort')) { ?>
		<input type="hidden" name="action" value="details.efforttracking"/>
		<button type="submit" name="start_tracking" value="true"><?= eL('starteffort') ?></button>
		<br />
		<label for="effort_to_add"><?= eL('manualeffort') ?></label>
		<input id="effort_to_add" name="effort_to_add" class="text" type="text" size="5" maxlength="100" value="00:00" title="hh:mm"/>
		<input type="text" name="effort_description" size="10" placeholder="optional" title="optional description"/>
		<button type="submit" name="manual_effort" value="true"><?= eL('addeffort') ?></button>
    <?php } ?>
		<table class="userlist history">
		<thead>
			<tr>
				<th><?= eL('date') ?></th>
				<th><?= eL('user') ?></th>
				<th><?= eL('effort') ?> (H:M)</th>
				<th></th>
				<th><?= eL('comment') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($effort->details as $details): ?>
            <tr>
                <td><?php echo Filters::noXSS(formatDate($details['date_added'], true)); ?></td>
                <td><?php echo tpl_userlink($details['user_id']); ?></td>
                <td><?php if($details['effort'] == 0 && $details['end_timestamp']==false): ?>
                    <?= eL('trackinginprogress') ?> (<?php
                    	echo effort::secondsToString(time()-$details['start_timestamp'], $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
                    ?>)
                    <?php else:
                    	echo effort::secondsToString($details['effort'], $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
                    endif;
					?></td>
                <td><?php if($user->id == $details['user_id'] & is_null($details['end_timestamp'])): ?>
                    <button type="submit" name="stop_tracking" value="true"><?= eL('endeffort') ?></button>
                    <button type="submit" name="cancel_tracking" value="true"><?= eL('cleareffort') ?></button>
                <?php endif; ?></td>
				<td><?= Filters::noXSS($details['description']) ?></td>
            </tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	</form>
	<!-- a href with target flyspraytimers for webbrowsers with disabled javascript -->
	<a href="/mytimers.php" onclick="javascript:window.open('/mytimers.php','flyspraytimers','innerWidth=320,innerHeight=300,scrollbars=no');return false;" target="flyspraytimers">watch my effort tracking timers</a>
</div>
