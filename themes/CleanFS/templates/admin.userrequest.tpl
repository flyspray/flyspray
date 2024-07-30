<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS(L('pendingrequests')); ?></h2>

<?php if (!count($pendings)): ?>
	<?php echo Filters::noXSS(L('nopendingreq')); ?>
<?php else: ?>
	<table class="requests">
	<thead>
	<tr>
		<th><?php echo Filters::noXSS(L('eventdesc')); ?></th>
		<th><?php echo Filters::noXSS(L('requestedby')); ?></th>
		<th><?php echo Filters::noXSS(L('daterequested')); ?></th>
		<th><?php echo Filters::noXSS(L('emailaddress')); ?></th>
		<th class="pm-buttons"> </th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($pendings as $req): ?>
	<tr>
		<td>
		New User Request
		</td>
		<td><?php echo tpl_userlink($req['submitted_by']); ?></td>
		<td><?php echo Filters::noXSS(formatDate($req['time_submitted'], true)); ?></td>
		<td><?php echo Filters::noXSS($req['reason_given']); ?></td>
		<td>
			<?php echo tpl_form(Filters::noXSS(CreateUrl('edituser', $req['submitted_by'])), null, null, null, 'style="display:inline"'); ?>
				<input type="submit" class="button" value="<?php echo Filters::noXSS(L('accept')); ?>">
				<input type="hidden" name="action" value="approve.user"/>
				<input type="hidden" name="user_id" value="<?php echo $req['submitted_by']; ?>"/>
				<input type="hidden" name="account_enabled" value="1"/>
			</form>

			<a class="button" onclick="showhidestuff('denyform<?php echo Filters::noXSS($req['request_id']); ?>');return false;"><?php echo Filters::noXSS(L('deny')); ?></a>
			<div id="denyform<?php echo Filters::noXSS($req['request_id']); ?>" class="denyform">
				<?php echo tpl_form(Filters::noXSS(CreateUrl('admin','userrequest'))); ?>
				<div>
					<label for="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>" class="inline"><?php echo Filters::noXSS(L('reasonfordeinal')); ?></label>
					<textarea class="txta-small" cols="40" rows="5" name="deny_reason" id="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>"></textarea>
					<div class="buttons">
					<input type="hidden" name="action" value="denyuserreq" />
					<input type="hidden" name="req_id" value="<?php echo Filters::noXSS($req['request_id']); ?>" />
					<button type="submit"><?php echo Filters::noXSS(L('deny')); ?></button>
					</div>
				</div>
				</form>
			</div>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
<?php endif; ?>
</div>
