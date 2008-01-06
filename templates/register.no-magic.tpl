<fieldset class="box">
<legend>{L('registernewuser')}</legend>

<form action="{CreateUrl('register')}" method="post" id="registernewuser">
  <table class="box">
    <tr>
      <td><label for="username">{L('username')}</label></td>
      <td><input class="required text" value="{Req::val('user_name')}" id="username" name="user_name" type="text" size="20" maxlength="32" onblur="checkname(this.value);" /> {L('validusername')}<br /><strong><span id="errormessage"></span></strong></td>
    </tr>
    <tr>
      <td><label for="realname">{L('realname')}</label></td>
      <td><input class="required text" value="{Req::val('real_name')}" id="realname" name="real_name" type="text" size="30" maxlength="100" /></td>
    </tr>
    <tr>
      <td><label for="emailaddress">{L('emailaddress')}</label></td>
      <td><input id="emailaddress" value="{Req::val('email_address')}" name="email_address" class="required text" type="text" size="20" maxlength="100" /></td>
    </tr>
    <tr>
      <td><label for="jabberid">{L('jabberid')}</label></td>
      <td><input id="jabberid" value="{Req::val('jabber_id')}" name="jabber_id" type="text" class="text" size="20" maxlength="100" /></td>
    </tr>
    <tr>
      <td><label for="notify_type">{L('notifications')}</label></td>
      <td>
        <select id="notify_type" name="notify_type">
          {!tpl_options($fs->GetNotificationOptions(), Req::val('notify_type'))}
        </select>
      </td>
    </tr>
    <tr>
      <td><label for="time_zone">{L('timezone')}</label></td>
      <td>
        <select id="time_zone" name="time_zone">
          <?php
            $times = array();
            for ($i = -12; $i <= 13; $i++) {
              $times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
            }
          ?>
          {!tpl_options($times, Req::val('time_zone', 0))}
        </select>
      </td>
    </tr>
  </table>
 <div>
    <input type="hidden" name="action" value="register.sendcode" />
    <button type="submit" name="buSubmit" id="buSubmit">{L('sendcode')}</button>
  </div>

  <p>{!L('note')}</p>
</form>
</fieldset>
