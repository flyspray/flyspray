<?php
if(isset($theuser->infos['eventtypes'])){
	$eventpref=$theuser->infos['eventtypes'];
}else{
	$eventpref=array_keys($events);
	$usereventpref=array_keys($user_events);
}

$event_chunks = array_chunk($events, ceil(count($events) / 3), true);

?>
<div class="box">
	<h2><?php echo Filters::noXSS(L('eventlog')); ?></h2>

	<form action="<?php echo Filters::noXSS(CreateURL('reports', $proj->id)); ?>" method="get">

	<fieldset>
		<legend><?php echo Filters::noXSS(L('eventtypes')); ?></legend>

		<h4><?php echo Filters::noXSS(L('Tasks')); ?></h4>

		<div class="checks_wrap">
<?php
	foreach ($event_chunks as $event_chunk) {
?>
			<ul class="form_elements checks_list">
<?php
		foreach ($event_chunk as $event_id => $event_text) {
?>
				<li>
					<div class="valuewrap">
						<?= tpl_checkbox('events[]', in_array($event_id, $eventids), 'eventtype_' . $event_id, $event_id); ?>
					</div>
					<label for="eventtype_<?= $event_id; ?>"><?= $event_text; ?></label>
				</li>
<?php
		}
?>
			</ul>
<?php
	}
?>
		</div>

		<h4><?php echo Filters::noXSS(L('users')); ?></h4>

		<ul class="form_elements checks_list">
<?php
		foreach ($user_events as $event_id => $event_text) {
?>
			<li>
				<div class="valuewrap">
					<?= tpl_checkbox('events[]', in_array($event_id, $eventids), 'eventtype_' . $event_id, $event_id); ?>
				</div>
				<label for="eventtype_<?= $event_id; ?>"><?= $event_text; ?></label>
			</li>
<?php
		}
?>
		</ul>
	</fieldset>

	<ul class="form_elements">
		<li>
			<label for="fromdate"><?php echo Filters::noXSS(L('from')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<div class="valuemultipair">
						<?php echo tpl_datepicker('fromdate'); ?>
					</div>
					<div class="valuemultipair">
						<?php echo tpl_datepicker('todate', L('to')); ?>
					</div>
				</div>
			</div>
		</li>
		<li>
			<label for="event_number"><?php echo Filters::noXSS(L('show')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<select name="event_number" id="event_number">
					<?php
					# set 20 to 25 like in tasks_per_page because we use same settings here too
					echo tpl_options(array(10 => 10, 25 => 25, 50 => 50, 100 => 100, 200 => 200),
						Req::val('event_number', isset($theuser->infos['tasks_perpage']) ? $theuser->infos['tasks_perpage'] : 50)); ?>
					</select>
					<span><?php echo Filters::noXSS(L('events')); ?></span>
				</div>
			</div>
		</li>
	</ul>

	<div class="buttons">
		<input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
		<input type="hidden" name="do" value="reports" />
		<button type="submit" name="submit"><?php echo Filters::noXSS(L('show')); ?></button>
	</div>
</form>

<?php if ($historycount): ?>
	<div class="pagination">
		<p><?php echo sprintf('Showing Events %d - %d of %d', $offset + 1, ($offset + $perpage > $historycount ? $historycount : $offset + $perpage), $historycount); ?></p>

		<?php echo pagenums($pagenum, $perpage, $historycount, 'reports', $proj->id); ?>
	</div>

	<table id="eventlist_table">
	<thead>
	<tr>
		<th>
			<a href="<?php echo Filters::noXSS(CreateURL('reports', $proj->id, null, array('sort' => (Req::val('order') == 'type' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'type') + $_GET)); ?>">
				<?php echo Filters::noXSS(L('event')); ?>
			</a>
		</th>
		<th>
			<a href="<?php echo Filters::noXSS(CreateURL('reports', $proj->id, null, array('sort' => (Req::val('order') == 'user' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'user') + $_GET)); ?>">
				<?php echo Filters::noXSS(L('user')); ?>
			</a>
		</th>
		<th>
			<a href="<?php echo Filters::noXSS(CreateURL('reports', $proj->id, null, array('sort' => (Req::val('order') == 'date' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'date') + $_GET)); ?>">
				<?php echo Filters::noXSS(L('eventdate')); ?>
			</a>
		</th>
		<th><?php echo Filters::noXSS(L('summary')); ?></th>
	</tr>
	</thead>
	<tbody>
<?php foreach ($histories as $history): ?>
	<?php if (isset($events[$history['event_type']])): ?>
	<tr class="severity1"><?php /* just for different colors */ ?>
		<td>
			<span class="fas fa-<?php echo (($history['event_type'] == 30 || $history['event_type'] == 31) ? 'user' : 'clipboard-list'); ?> fa-lg"></span><?php echo Filters::noXSS($events[$history['event_type']]); ?>
		</td>
	<?php else: ?>
	<tr class="severity2">
		<td>
			<span class="fas fa-<?php echo (($history['event_type'] == 30 || $history['event_type'] == 31) ? 'user' : 'clipboard-list'); ?> fa-lg"></span><?php echo Filters::noXSS($user_events[$history['event_type']]); ?>
		</td>
	<?php endif; ?>
		<td><?php echo tpl_userlink($history['user_id']); ?></td>
		<td><?php echo Filters::noXSS(formatDate($history['event_date'], true)); ?></td>
		<?php if ($history['event_type'] == 30 ||
					$history['event_type'] == 31):
				$user_data = unserialize($history['new_value']); ?>
		<td>
			<a href="javascript:showhidestuff('h<?php echo Filters::noXSS($history['history_id']); ?>')"><?php echo Filters::noXSS(L('detailedinfo')); ?></a>
			<div class="hide popup" id="h<?php echo Filters::noXSS($history['history_id']); ?>">
				<table>
					<tr>
					<th><?php echo Filters::noXSS(L('username')); ?></th>
					<td><?php echo Filters::noXSS($user_data['user_name']); ?></td>
					</tr>
					<tr>
					<th><?php echo Filters::noXSS(L('realname')); ?></th>
					<td><?php echo Filters::noXSS($user_data['real_name']); ?></td>
					</tr>
					<tr>
					<th><?php echo Filters::noXSS(L('email')); ?></th>
					<td><?php echo Filters::noXSS($user_data['email_address']); ?></td>
					</tr>
					<tr>
					<th><?php echo Filters::noXSS(L('jabber')); ?></th>
					<td><?php echo Filters::noXSS($user_data['jabber_id']); ?></td>
					</tr>
				</table>
			</div>
		</td>
	<?php else: ?>
		<td><?php echo tpl_tasklink($history); ?></td>
	<?php endif; ?>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>

	<div class="pagination">
		<p><?php echo sprintf('Showing Events %d - %d of %d', $offset + 1, ($offset + $perpage > $historycount ? $historycount : $offset + $perpage), $historycount); ?></p>

		<?php echo pagenums($pagenum, $perpage, $historycount, 'reports', $proj->id); ?>
	</div>

<?php else: ?>
	<div class="noresult"><strong><?= eL('noresults') ?></strong></div>
<?php endif; ?>
</div>
