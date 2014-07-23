<form action="<?php if ($do == 'admin'): ?>{CreateURL($do, 'editallusers')}<?php else: ?>{$_SERVER['SCRIPT_NAME']}<?php endif; ?>" method="post" id="editallusers">
  <ul class="form_elements">
    <li class="required">
      <?php if ($do == 'admin'): ?>
        <input type="hidden" name="action" value="admin.editallusers" />
        <input type="hidden" name="do" value="admin" />
        <input type="hidden" name="area" value="editallusers" />
      <?php endif; ?>
    </li>

	<table class="bulkedituser">
		<tr class="account_header">
		        <td><label for=""></label></td>
		        <td><label for="realname"><b>{L('realname')}</b></label></td>
        		<td><label for="username"><b>{L('username')}</b></label></td>
		        <td><label for="emailaddress"><b>{L('emailaddress')}</b></label></td>
		</tr>
	<?php
		/* FIXME: each TR should have an onclick that selects/deselects the checkbox for that user */
		foreach (Flyspray::ListUsers() as $usr)
		{ 
			if ( $usr['account_enabled'] )
			{
				echo '<tr class="account_enabled">';
			}
			else
			{
				echo '<tr class="account_disabled">';
			}
        ?>


		        <td><input type="checkbox" name="checkedUsers[]" value="<?php echo $usr['user_id']; ?>"></td>
		        <td><a href="<?php echo CreateURL('edituser', $usr['user_id'] ); ?>"><?php echo $usr['real_name']; ?></a></td>
		        <td><?php echo $usr['user_name']; ?></td>
		        <td><?php echo $usr['email_address']; ?></td>
		</tr>

	<?php
		}
	?>

    <br />

    </table>

    <br />

    <button type="submit" id="buSubmit" name="enable">{L('enableaccounts')}</button>
    <button type="submit" id="buSubmit" name="disable">{L('disableaccounts')}</button>
    <button type="submit" id="buSubmit" name="delete">{L('deleteaccounts')}</button>

<!-- FIXME: Should still add these to bulk edit, but hasn't been done yet
    <li>
      <label for="notify_type">{L('notifications')}</label>
      <select id="notify_type" name="notify_type">
        {!tpl_options($fs->GetNotificationOptions(), Req::val('notify_type'))}
      </select>
    </li>

    <li>
      <label for="time_zone">{L('timezone')}</label>
      <select id="time_zone" name="time_zone">
        <?php
          $times = array();
          for ($i = -12; $i <= 13; $i++) {
            $times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
          }
        ?>
        {!tpl_options($times, Req::val('time_zone', 0))}
      </select>
    </li>

    <?php if (isset($groups)): ?>
    <li>
      <label for="groupin">{L('globalgroup')}</label>
      <select id="groupin" class="adminlist" name="group_in">
        {!tpl_options($groups, Req::val('group_in'))}
      </select>
    </li>
    <?php endif; ?>

  </ul>
  <p><button type="submit" id="buSubmit">{L('Update Accounts')}</button></p>
-->
</form>
