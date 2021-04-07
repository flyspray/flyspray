<h3><?= eL('registernewuser') ?></h3>
<div class="box">
    <!--
<?php echo tpl_form(Filters::noXSS($baseurl).'index.php','registernewuser',null,null,'id="registernewuser"'); ?>
    -->
<?php echo tpl_form(Filters::noXSS(createUrl('registernewuser')), null, null, null, 'id="registernewuser"'); ?>
  <p><?= eL('entercode') ?></p>
  <ul class="form_elements wide">
    <li>
      <label for="confirmation_code"><?= eL('confirmationcode') ?></label>
      <input id="confirmation_code" class="text" name="confirmation_code" value="<?php echo Filters::noXSS(Req::val('confirmation_code')); ?>" type="text" size="20" maxlength="20" />
    </li>
    <li>
      <label for="user_pass"><?= eL('password') ?></label>
      <input id="user_pass" class="password" name="user_pass" value="<?php echo Filters::noXSS(Req::val('user_pass')); ?>" type="password" size="20" maxlength="100" /> <em><?= eL('minpwsize') ?></em>
    </li>
    <?php if($fs->prefs['repeat_password']): ?>
    <li>
      <label for="user_pass2"><?= eL('confirmpass') ?></label>
      <input id="user_pass2" class="password" name="user_pass2" value="<?php echo Filters::noXSS(Req::val('user_pass2')); ?>" type="password" size="20" maxlength="100" />
    </li>
    <?php endif;?>
  </ul>
  <div>
    <input type="hidden" name="action" value="register.registeruser" />
    <input type="hidden" name="magic_url" value="<?php echo Filters::noXSS(Req::val('magic_url')); ?>" />
    <button type="submit" name="buSubmit"><?= eL('registeraccount') ?></button>
  </div>
</form>
</div>
