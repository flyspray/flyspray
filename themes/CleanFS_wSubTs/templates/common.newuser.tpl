<form action="<?php if ($do == 'admin'): ?>{CreateURL($do, 'newuser')}<?php else: ?>{$_SERVER['SCRIPT_NAME']}<?php endif; ?>" method="post" id="registernewuser">
  <ul class="form_elements">
    <li class="required">
      <?php if ($do == 'admin'): ?>
        <input type="hidden" name="action" value="admin.newuser" />
        <input type="hidden" name="do" value="admin" />
        <input type="hidden" name="area" value="newuser" />
      <?php else: ?>
        <input type="hidden" name="action" value="register.newuser" />
      <?php endif; ?>
      <label for="username">{L('username')}*</label>
      <input id="username" name="user_name" value="{Req::val('user_name')}" class="required text" type="text" size="20" maxlength="32" onblur="checkname(this.value);" />
      <br /><span id="errormessage"></span>
    </li>

    <li>
      <label for="userpass">{L('password')}</label>
      <input id="userpass" class="password" name="user_pass" value="{Req::val('user_pass')}" type="password" size="20" maxlength="100" /> <em>{L('minpwsize')}</em>
    </li>

    <li>
      <label for="userpass2">{L('confirmpass')}</label>
      <input id="userpass2" class="password" name="user_pass2" value="{Req::val('user_pass2')}" type="password" size="20" maxlength="100" /><br />
      <span class="note">{L('leaveemptyauto')}</span>
    </li>

    <li class="required">
      <label for="realname">{L('realname')}*</label>
      <input id="realname" name="real_name" class="required text" value="{Req::val('real_name')}" type="text" size="20" maxlength="100" />
    </li>

    <li class="required">
      <label for="emailaddress">{L('emailaddress')}*</label>
      <input id="emailaddress" name="email_address" class="text required" value="{Req::val('email_address')}" type="text" size="20" maxlength="100" />
    </li>

    <li>
      <label for="jabberid">{L('jabberid')}</label>
      <input id="jabberid" name="jabber_id" class="text" type="text" value="{Req::val('jabber_id')}" size="20" maxlength="100" />
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
  <p><button type="submit" id="buSubmit">{L('registeraccount')}</button></p>
</form>
