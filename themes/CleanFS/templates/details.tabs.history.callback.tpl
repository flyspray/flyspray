<?php if ($details && count($histories)): ?>
<table class="taskeventhistorydetails">
<tr>
	<th><?php echo Filters::noXSS(L('previousvalue')); ?></th>
	<th><?php echo Filters::noXSS(L('newvalue')); ?></th>
</tr>
<tr>
	<td><?php echo $details_previous; ?></td>
	<td><?php echo $details_new; ?></td>
</tr>
</table>
<?php else: ?>
<table id="taskeventhistory">
<thead>
<tr>
	<th class="date"><?php echo Filters::noXSS(L('eventdate')); ?></th>
	<th class="user"><?php echo Filters::noXSS(L('user')); ?></th>
	<th class="event"><?php echo Filters::noXSS(L('event')); ?></th>
</tr>
</thead>
<tbody>
<?php foreach($histories as $history): ?>
<tr>
	<td class="taskevent_date">
		<?php echo Filters::noXSS(formatDate($history['event_date'], true)); ?>
	</td>
	<td class="taskevent_user">
<?php if($fs->prefs['enable_avatars'] == 1): ?>
		<?php echo tpl_userlinkavatar($history['user_id'], 24) ?> <?php echo tpl_userlink($history['user_id']); ?>
<?php else: ?>
		<?php echo tpl_userlink($history['user_id']); ?>
<?php endif; ?>
	</td>
	<td class="taskevent_event">
		<?php
			echo event_description($history);

			if (
				($history['event_type'] == 3 && $history['field_changed'] != '')
				||
				$history['event_type'] == 5
				||
				$history['event_type'] == 6
			){ ?>
		<div id="taskevent_details_<?= $history['history_id'] ?>" class="box"></div>
		<?php
			}
		?>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
