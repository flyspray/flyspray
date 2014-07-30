<form action="<?php if ($do == 'admin'): ?><?php echo Filters::noXSS(CreateURL($do, 'newuserbulk')); ?><?php else: ?><?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?><?php endif; ?>" method="post" id="registernewuser">
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
      <b><?php echo Filters::noXSS(L('bulkuserstoadd')); ?>:</b>
    </li>
    <table class="bulkuser">
      <tr>
        <td><label for="realname"><?php echo Filters::noXSS(L('realname')); ?></label></td>
        <td><label for="username"><?php echo Filters::noXSS(L('username')); ?></label></td>
        <td><label for="emailaddress"><?php echo Filters::noXSS(L('emailaddress')); ?></label></td>
      </tr>
    <br />


<?php for ($i = 0 ; $i < 10 ; $i++) { ?>
    <!-- User <?php echo Filters::noXSS($i); ?> -->
    <tr>
      <td><input id="realname" name="real_name<?php echo Filters::noXSS($i); ?>" class="text" value="<?php echo Filters::noXSS(Req::val('real_name')); ?>" type="text" size="20" maxlength="100" onblur="this.form.elements['user_name<?php echo Filters::noXSS($i); ?>'].value = this.form.elements['real_name<?php echo Filters::noXSS($i); ?>'].value.replace(/ /g,'');"/></td>
      <td><input id="username" name="user_name<?php echo Filters::noXSS($i); ?>" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" class="text" type="text" size="20" maxlength="32"  onblur="checkname(this.value); "/></td>
      <td><input id="emailaddress" name="email_address<?php echo Filters::noXSS($i); ?>" class="text" value="<?php echo Filters::noXSS(Req::val('email_address')); ?>" type="text" size="20" maxlength="100" /></td>
    <tr>
     <tr></tr>
<?php } ?>
    </table>


    <br />

    <li>
      <b><?php echo Filters::noXSS(L('optionsforallusers')); ?>:</b>
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

    <?php if (isset($groups)): ?>
    <li>
      <label for="groupin"><?php echo Filters::noXSS(L('globalgroup')); ?></label>
      <select id="groupin" class="adminlist" name="group_in">
        <?php echo tpl_options($groups, Req::val('group_in')); ?>

      </select>
    </li>
    <?php endif; ?>

  </ul>
  <p><button type="submit" id="buSubmit"><?php echo Filters::noXSS(L('registerbulkaccount')); ?></button></p>
</form>
