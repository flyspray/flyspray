<form id="login" action="{$baseurl}index.php?do=authenticate" method="post">
<div id="login_input">
  <label for="lbl_user_name">{L('username')}</label>
  <input class="text" type="text" id="lbl_user_name" name="user_name" size="17" maxlength="30" />
  
  <label for="lbl_password">{L('password')}</label>
  <input class="password" type="password" id="lbl_password" name="password" size="17" maxlength="30" />
  
  <input type="hidden" name="return_to" value="{$_SERVER['REQUEST_URI']}" />

  <input type="submit" value="{L('login')}" name="login" id="login_button" />
</div>
<div id="login_links">
    <div id="login_hidelink">
        <a href="#" onclick="return toggleLoginBox(document.getElementById('show_loginbox'));"><small>hide</small></a>
    </div>
    <div class="remember_me">
        <label for="lbl_remember">{L('rememberme')}</label>
        <input type="checkbox" id="lbl_remember" name="remember_login" />
    </div>
    <?php $activeclass = ' class="active" '; ?>
    <?php if ($user->isAnon() && $fs->prefs['anon_reg']): ?>
      <a id="registerlink"
        <?php if(isset($_GET['do']) and $_GET['do'] == 'register') echo $activeclass; ?>
        href="{CreateURL('register','')}">{L('register')}</a>
    <?php endif; ?>
    <?php if ($user->isAnon() && $fs->prefs['user_notify']): ?>
      <a id="forgotlink"
        <?php if(isset($_GET['do']) and $_GET['do'] == 'lostpw') echo $activeclass; ?>
        href="{CreateURL('lostpw','')}">{L('lostpassword')}</a>
    <?php else: ?>
      <a id="forgotlink" href="mailto:{implode(',', $admin_emails)}?subject={rawurlencode(L('lostpwforfs'))}&amp;body={rawurlencode(L('lostpwmsg1'))}{$baseurl}{rawurlencode(L('lostpwmsg2'))}<?php
              if(isset($_SESSION['failed_login'])):
              ?>{rawurlencode($_SESSION['failed_login'])}<?php
              else:
              ?>&lt;{rawurlencode(L('yourusername'))}&gt;<?php
              endif;
              ?>{rawurlencode(L('regards'))}">{L('lostpassword')}</a>
      <script type="text/javascript">var link = document.getElementById('forgotlink');link.href=link.href.replace(/#/g,"@");</script>
    <?php endif; ?>
</div>
</form>
