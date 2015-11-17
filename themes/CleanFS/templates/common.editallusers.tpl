<script>
function toggleCheckbox(id)
{
	var el = document.getElementById(id);
	if (el != null) {
		if (el.checked) {
			el.checked = false;
		} else {
			el.checked = true;
		}
	}
}
</script>
<?php 
if ($do == 'admin'): echo tpl_form(Filters::noXSS(CreateURL($do, 'editallusers')), null, null, null, 'id="editallusers"');
               else: echo tpl_form(Filters::noXSS($_SERVER['SCRIPT_NAME']), null, null, null, 'id="editallusers"');
endif;
if ($do == 'admin'): ?>
	<input type="hidden" name="action" value="admin.editallusers" />
	<input type="hidden" name="do" value="admin" />
	<input type="hidden" name="area" value="editallusers" />
<?php endif; ?>
<table class="bulkedituser">
	<thead>
	<tr class="account_header">
		<th></th>
		<th><?php echo Filters::noXSS(L('realname')); ?></th>
		<th><?php echo Filters::noXSS(L('username')); ?></th>
		<th><?php echo Filters::noXSS(L('emailaddress')); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach (Flyspray::ListUsers() as $usr) {
		if ( $usr['account_enabled'] ) {
			echo '<tr class="account_enabled" onclick="toggleCheckbox('.$usr['user_id'].')">';
		} else {
			echo '<tr class="account_disabled" onclick="toggleCheckbox('.$usr['user_id'].')">';
		} ?>
		<td><input id="<?php echo $usr['user_id'] ?>" onclick="event.stopPropagation()" type="checkbox" name="checkedUsers[]" value="<?php echo $usr['user_id']; ?>"></td>
		<td><a href="<?php echo CreateURL('edituser', $usr['user_id'] ); ?>"><?php echo $usr['real_name']; ?></a></td>
		<td><?php echo $usr['user_name']; ?></td>
		<td><?php echo $usr['email_address']; ?></td>
	</tr>
	<?php } ?>
	</tbody>
</table>

<button type="submit" id="buSubmit" name="enable"><?php echo Filters::noXSS(L('enableaccounts')); ?></button>
<button type="submit" id="buSubmit" name="disable"><?php echo Filters::noXSS(L('disableaccounts')); ?></button>
<button type="submit" id="buSubmit" name="delete"><?php echo Filters::noXSS(L('deleteaccounts')); ?></button>

<!-- TODO Should still add these to bulk edit, but hasn't been done yet
<ul class="form_elements">
<li class="required">
      <label for="notify_type"><?php echo Filters::noXSS(L('notifications')); ?></label>
      <select id="notify_type" name="notify_type">
        <?php echo tpl_options($fs->GetNotificationOptions(), Req::val('notify_type')); ?>

      </select>
</li>
<li>
      <label for="time_zone"><?php echo Filters::noXSS(L('timezone')); ?></label>
      <select id="time_zone" name="time_zone">
        <?php
          $times = array();
          for ($i = -12; $i <= 13; $i++) {
            $times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
          }
        ?>
        <?php echo tpl_options($times, Req::val('time_zone', 0)); ?>

      </select>
</li>

    <?php if (isset($groups)): ?>
    <li>
      <label for="groupin"><?php echo Filters::noXSS(L('globalgroup')); ?></label>
      <select id="groupin" class="adminlist" name="group_in">
        <?php echo tpl_options($groups, Req::val('group_in')); ?>

      </select>
    </li>
    <?php endif; ?>

  </ul>
  <p><button type="submit" id="buSubmit"><?php echo Filters::noXSS(L('Update Accounts')); ?></button></p>
-->
</form>
