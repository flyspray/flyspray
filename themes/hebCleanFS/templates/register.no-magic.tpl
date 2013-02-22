<h3>{L('registernewuser')}</h3>
<div class="box">

<form action="{CreateUrl('register')}" method="post" id="registernewuser">
  <ul class="form_elements wide">
    <li>
      <label for="username">{L('username')}</label>
      <input class="required text" value="{Req::val('user_name')}" id="username" name="user_name" type="text" size="20" maxlength="32" onblur="checkname(this.value);" /> {L('validusername')}<br /><strong><span id="errormessage"></span></strong>
    </li>

    <li>
      <label for="realname">{L('realname')}</label>
      <input class="required text" value="{Req::val('real_name')}" id="realname" name="real_name" type="text" size="30" maxlength="100" />
    </li>

    <li>
      <label for="emailaddress">{L('emailaddress')}</label>
      <input id="emailaddress" value="{Req::val('email_address')}" name="email_address" class="required text" type="text" size="20" maxlength="100" />
    </li>

    <li>
      <label for="jabberid">{L('jabberid')}</label>
      <input id="jabberid" value="{Req::val('jabber_id')}" name="jabber_id" type="text" class="text" size="20" maxlength="100" />
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
  </ul>
 <div>
    <input type="hidden" name="action" value="register.sendcode" />
    <button type="submit" name="buSubmit" id="buSubmit">{L('sendcode')}</button>
  </div>
  <br />
  <p>{!L('note')}</p>
</form>
</div>
