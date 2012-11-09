<h1>FIXME: This page is still being built and does not yet workd</h1>


<!--

  ==================
  Spec for this page
  ==================

  Show all users with:
  - Name, email, etc
  - Groups
  - Disabled or enabled
  - ??

  Functionlity:
  - Bulk enable/disable users view checkbox & dropdown
  - Bulk reset password
  - Bulk send user password
  - Bulk change group
  - Bulk delete users

-->


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
		foreach (Flyspray::ListUsers() as $user)
		{ 
			if ( $user['account_enabled'] )
			{
				echo '<tr class="account_enabled">';
			}
			else
			{
				echo '<tr class="account_disabled">';
			}
        ?>


		        <td><input type="checkbox" name"<?= $user['user_id'] ?>"></td>
		        <td><a href=<?= CreateURL('edituser', $user['user_id'] )?>><?= $user['real_name'] ?></a></td>
		        <td><?= $user['user_name'] ?></td>
		        <td><?= $user['email_address'] ?></td>
		</tr>

	<?php
		}
	?>

    <br />

    </table>


    <br />

    <button type="submit" id="buSubmit">{L('Enable Accounts')}</button>
    <button type="submit" id="buSubmit">{L('Disable Accounts')}</button>
    <button type="submit" id="buSubmit">{L('Delete Accounts')}</button>

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
</form>
