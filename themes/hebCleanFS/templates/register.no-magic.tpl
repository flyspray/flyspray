<h3><?php echo Filters::noXSS(L('registernewuser')); ?></h3>
<div class="box">

<form action="<?php echo Filters::noXSS(CreateUrl('register')); ?>" method="post" id="registernewuser">
  <ul class="form_elements wide">
    <li>
      <label for="username"><?php echo Filters::noXSS(L('username')); ?></label>
      <input class="required text" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" id="username" name="user_name" type="text" size="20" maxlength="32" onblur="checkname(this.value);" /> <?php echo Filters::noXSS(L('validusername')); ?><br /><strong><span id="errormessage"></span></strong>
    </li>

    <li>
      <label for="realname"><?php echo Filters::noXSS(L('realname')); ?></label>
      <input class="required text" value="<?php echo Filters::noXSS(Req::val('real_name')); ?>" id="realname" name="real_name" type="text" size="30" maxlength="100" />
    </li>

    <li>
      <label for="emailaddress"><?php echo Filters::noXSS(L('emailaddress')); ?></label>
      <input id="emailaddress" value="<?php echo Filters::noXSS(Req::val('email_address')); ?>" name="email_address" class="required text" type="text" size="20" maxlength="100" />
    </li>

    <li>
      <label for="jabberid"><?php echo Filters::noXSS(L('jabberid')); ?></label>
      <input id="jabberid" value="<?php echo Filters::noXSS(Req::val('jabber_id')); ?>" name="jabber_id" type="text" class="text" size="20" maxlength="100" />
    </li>

    <li>
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
  </ul>
 <div>
    <input type="hidden" name="action" value="register.sendcode" />
    <button type="submit" name="buSubmit" id="buSubmit"><?php echo Filters::noXSS(L('sendcode')); ?></button>
  </div>
  <br />
  <p><?php echo L('note'); ?></p>
</form>
</div>
