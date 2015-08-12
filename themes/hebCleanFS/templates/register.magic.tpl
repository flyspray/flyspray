<h3><?php echo Filters::noXSS(L('registernewuser')); ?></h3>
<div class="box">
  
<form action="<?php echo Filters::noXSS($baseurl); ?>index.php" name="registernewuser" method="post" id="registernewuser">
  <p><?php echo Filters::noXSS(L('entercode')); ?></p>
  <ul class="form_elements wide">
    <li>
      <label for="confirmation_code"><?php echo Filters::noXSS(L('confirmationcode')); ?></label>
      <input id="confirmation_code" class="text" name="confirmation_code" value="<?php echo Filters::noXSS(Req::val('confirmation_code')); ?>" type="text" size="20" maxlength="20" />
    </li>

    <li>
      <label for="user_pass"><?php echo Filters::noXSS(L('password')); ?></label>
      <input id="user_pass" class="password" name="user_pass" value="<?php echo Filters::noXSS(Req::val('user_pass')); ?>" type="password" size="20" maxlength="100" /> <em><?php echo Filters::noXSS(L('minpwsize')); ?></em>
    </li>

    <li>
      <label for="user_pass2"><?php echo Filters::noXSS(L('confirmpass')); ?></label>
      <input id="user_pass2" class="password" name="user_pass2" value="<?php echo Filters::noXSS(Req::val('user_pass2')); ?>" type="password" size="20" maxlength="100" />
    </li>
  </ul>

    <div>
        <input type="hidden" name="action" value="register.registeruser" />
        <input type="hidden" name="magic_url" value="<?php echo Filters::noXSS(Req::val('magic_url')); ?>" />
        <button type="submit" name="buSubmit"><?php echo Filters::noXSS(L('registeraccount')); ?></button>
    </div>
</form>

</div>
