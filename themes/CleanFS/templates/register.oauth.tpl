<h3><?php echo Filters::noXSS(L('register')); ?></h3>
<div class="box">

<form action="index.php?do=oauth" method="post" id="registernewuser">
  <ul class="form_elements">
    <li>
      <label for="username"><?php echo Filters::noXSS(L('username')); ?></label>
      <input class="required text" value="<?php echo Filters::noXSS($username); ?>" id="user_name" name="username" type="text" size="20" maxlength="32"  /> <?php echo Filters::noXSS(L('validusername')); echo '<span class="warning"> ' . Filters::noXSS(L('usernametaken')) . '</span>'; ?>
    </li>
  </ul>
 <div>
    <button type="submit" name="buSubmit" id="buSubmit"><?php echo Filters::noXSS(L('register')); ?></button>
  </div>
</form>
</div>
