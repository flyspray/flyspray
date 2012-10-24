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


<form action="<?php if ($do == 'admin'): ?>{CreateURL($do, 'editallusers')}<?php else: ?>{$_SERVER['SCRIPT_NAME']}<?php endif; ?>" method="post" id="registernewuser">
  <ul class="form_elements">
    <li class="required">
      <?php if ($do == 'admin'): ?>
        <input type="hidden" name="action" value="admin.newuserbulk" />
        <input type="hidden" name="do" value="admin" />
        <input type="hidden" name="area" value="newuserbulk" />
      <?php else: ?>
        <input type="hidden" name="action" value="register.newuserbulk" />
      <?php endif; ?>
    </li>

    <!-- Header -->
    <li>
      <b>{L('bulkuserstoadd')}:</b>
    </li>
    <table class="bulkuser">
      <tr>
        <td><label for="realname">{L('realname')}</label></td>
        <td><label for="username">{L('username')}</label></td>
        <td><label for="emailaddress">{L('emailaddress')}</label></td>
      </tr>
    <br />


<? for ($i = 0 ; $i < 10 ; $i++) { ?>
    <!-- User {$i} -->
    <tr>
      <td><input id="realname" name="real_name{$i}" class="text" value="{Req::val('real_name')}" type="text" size="20" maxlength="100" onblur="this.form.elements['user_name{$i}'].value = this.form.elements['real_name{$i}'].value.replace(/ /g,'');"/></td>
      <td><input id="username" name="user_name{$i}" value="{Req::val('user_name')}" class="text" type="text" size="20" maxlength="32"  onblur="checkname(this.value); "/></td>
      <td><input id="emailaddress" name="email_address{$i}" class="text" value="{Req::val('email_address')}" type="text" size="20" maxlength="100" /></td>
    <tr>
     <tr></tr>
<? } ?>
    </table>


    <br />

    <li>
      <b>{L('optionsforallusers')}:</b>
    </li>
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
  <p><button type="submit" id="buSubmit">{L('registerbulkaccount')}</button></p>
</form>
