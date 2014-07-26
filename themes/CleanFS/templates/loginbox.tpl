<form id="login" action="<?php echo Filters::noXSS($baseurl); ?>index.php?do=authenticate" method="post">
<div id="login_input">
  <label for="lbl_user_name"><?php echo Filters::noXSS(L('username')); ?></label>
  <input class="text" type="text" id="lbl_user_name" name="user_name" size="17" maxlength="30" />
  
  <label for="lbl_password"><?php echo Filters::noXSS(L('password')); ?></label>
  <input class="password" type="password" id="lbl_password" name="password" size="17" maxlength="30" />
  
  <input type="hidden" name="return_to" value="<?php echo Filters::noXSS($_SERVER['REQUEST_URI']); ?>" />

  <input type="submit" value="<?php echo Filters::noXSS(L('login')); ?>" name="login" id="login_button" />
</div>
<div id="login_links">
    <div id="login_hidelink">
        <a href="#" onclick="return toggleLoginBox(document.getElementById('show_loginbox'));"><small>hide</small></a>
    </div>
    <div class="remember_me">
        <label for="lbl_remember"><?php echo Filters::noXSS(L('rememberme')); ?></label>
        <input type="checkbox" id="lbl_remember" name="remember_login" />
    </div>
    <?php $activeclass = ' class="active" '; ?>
    <?php if ($user->isAnon() && $fs->prefs['anon_reg']): ?>
      <a id="registerlink"
        <?php if(isset($_GET['do']) and $_GET['do'] == 'register') echo $activeclass; ?>
        href="<?php echo Filters::noXSS(CreateURL('register','')); ?>"><?php echo Filters::noXSS(L('register')); ?></a>
    <?php endif; ?>
    <?php if ($user->isAnon() && !$fs->prefs['disable_lostpw']): ?>
    <?php if ($user->isAnon() && $fs->prefs['user_notify']): ?>
      <a id="forgotlink"
        <?php if(isset($_GET['do']) and $_GET['do'] == 'lostpw') echo $activeclass; ?>
        href="<?php echo Filters::noXSS(CreateURL('lostpw','')); ?>"><?php echo Filters::noXSS(L('lostpassword')); ?></a>
    <?php else: ?>
      <a id="forgotlink" href="mailto:<?php echo Filters::noXSS(implode(',', $admin_emails)); ?>?subject=<?php echo Filters::noXSS(rawurlencode(L('lostpwforfs'))); ?>&amp;body=<?php echo Filters::noXSS(rawurlencode(L('lostpwmsg1'))); ?><?php echo Filters::noXSS($baseurl); ?><?php echo Filters::noXSS(rawurlencode(L('lostpwmsg2'))); ?><?php
              if(isset($_SESSION['failed_login'])):
              ?><?php echo Filters::noXSS(rawurlencode($_SESSION['failed_login'])); ?><?php
              else:
              ?>&lt;<?php echo Filters::noXSS(rawurlencode(L('yourusername'))); ?>&gt;<?php
              endif;
              ?><?php echo Filters::noXSS(rawurlencode(L('regards'))); ?>"><?php echo Filters::noXSS(L('lostpassword')); ?></a>
      <script type="text/javascript">var link = document.getElementById('forgotlink');link.href=link.href.replace(/#/g,"@");</script>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php $return = '&return_to=' . base64_encode($_SERVER['REQUEST_URI']); 
      $providers = $conf['oauth']['enabled'];
?>
<div id="login_oauth">
    <?php if (in_array('github', $providers)): ?>
    <a class="oauth btn-github" href="index.php?do=oauth&provider=github<?php echo $return; ?>">
        <i class="fa fa-github"></i> Sign in with Github
    </a>
    <?php endif; ?>
    <?php if (in_array('google', $providers)): ?>
    <a class="oauth btn-google" href="index.php?do=oauth&provider=google<?php echo $return; ?>">
        <i class="fa fa-google"></i> Sign in with Google
    </a>
    <?php endif; ?>
    <?php if (in_array('facebook', $providers)): ?>
    <a class="oauth btn-facebook" href="index.php?do=oauth&provider=facebook<?php echo $return; ?>">
        <i class="fa fa-facebook"></i> Sign in with Facebook
    </a>
    <?php endif; ?>
</div>
</form>
