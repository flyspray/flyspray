<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('admintoolboxlong')); ?> :: <?php echo Filters::noXSS(L('preferences')); ?></h3>

  <form action="<?php echo Filters::noXSS(CreateURL('admin', 'prefs')); ?>" method="post">
  <ul id="submenu">
   <li><a href="#general"><?php echo Filters::noXSS(L('general')); ?></a></li>
   <li><a href="#userregistration"><?php echo Filters::noXSS(L('userregistration')); ?></a></li>
   <li><a href="#notifications"><?php echo Filters::noXSS(L('notifications')); ?></a></li>
   <li><a href="#lookandfeel"><?php echo Filters::noXSS(L('lookandfeel')); ?></a></li>
  </ul>
  
   <div id="general" class="tab">
      <ul class="form_elements">
        <li>
          <label for="pagetitle"><?php echo Filters::noXSS(L('pagetitle')); ?></label>
          <input id="pagetitle" name="page_title" type="text" class="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['page_title']); ?>" />
        </li>   

        <li>
          <label for="defaultproject"><?php echo Filters::noXSS(L('defaultproject')); ?></label>
          <select id="defaultproject" name="default_project">
            <?php echo tpl_options(array_merge(array(0 => L('allprojects')), Flyspray::listProjects()), $fs->prefs['default_project']); ?>

          </select>
        </li>   

        <li>
          <label for="langcode"><?php echo Filters::noXSS(L('language')); ?></label>
          <select id="langcode" name="lang_code">
            <?php echo tpl_options(Flyspray::listLangs(), $fs->prefs['lang_code'], true); ?>

          </select>
        </li>   
		
        <li>
          <label for="emailNoHTML"><?php echo Filters::noXSS(L('emailNoHTML')); ?></label>
        	<?php echo tpl_checkbox('emailNoHTML', $fs->prefs['emailNoHTML'], 'emailNoHTML'); ?>

        </li>			

        <li>
          <label for="dateformat"><?php echo Filters::noXSS(L('dateformat')); ?></label>
          <input id="dateformat" name="dateformat" type="text" class="text" size="40" maxlength="30" value="<?php echo Filters::noXSS($fs->prefs['dateformat']); ?>" />
        </li>   

        <li>
          <label for="dateformat_extended"><?php echo Filters::noXSS(L('dateformat_extended')); ?></label>
          <input id="dateformat_extended" name="dateformat_extended" class="text" type="text" size="40" maxlength="30" value="<?php echo Filters::noXSS($fs->prefs['dateformat_extended']); ?>" />
        </li>   

        <li>
          <label for="cache_feeds"><?php echo Filters::noXSS(L('cache_feeds')); ?></label>
          <select id="cache_feeds" name="cache_feeds">
          <?php echo tpl_options(array('0' => L('no_cache'), '1' => L('cache_disk'), '2' => L('cache_db')), $fs->prefs['cache_feeds']); ?>

          </select>
        </li>   
      </ul>
    </div>

    <div id="userregistration" class="tab">
      <ul class="form_elements">
        <li>
          <label for="allowusersignups"><?php echo Filters::noXSS(L('anonreg')); ?></label>
          <?php echo tpl_checkbox('anon_reg', $fs->prefs['anon_reg'], 'allowusersignups'); ?>

        </li>   

        <li>
          <label for="spamproof"><?php echo Filters::noXSS(L('spamproof')); ?></label>
          <?php echo tpl_checkbox('spam_proof', $fs->prefs['spam_proof'], 'spamproof'); ?>

        </li>   

        <li>
          <label for="notify_registration"><?php echo Filters::noXSS(L('notify_registration')); ?></label>
          <?php echo tpl_checkbox('notify_registration', $fs->prefs['notify_registration'], 'notify_registration'); ?>

        </li>   

        <li>
          <label for="defaultglobalgroup"><?php echo Filters::noXSS(L('defaultglobalgroup')); ?></label>
          <select id="defaultglobalgroup" name="anon_group">
            <?php echo tpl_options(Flyspray::listGroups(), $fs->prefs['anon_group']); ?>

          </select>
        </li>   
      </ul>
    </div>

    <div id="notifications" class="tab">
      <ul class="form_elements">
        <li>
          <label for="usernotify"><?php echo Filters::noXSS(L('forcenotify')); ?></label>
          <select id="usernotify" name="user_notify">
            <?php echo tpl_options(array(L('neversend'), L('userchoose'), L('email'), L('jabber')), $fs->prefs['user_notify']); ?>

          </select>
        </li>
      </ul>
      
      <fieldset><legend><?php echo Filters::noXSS(L('emailnotify')); ?></legend>
        <ul class="form_elements">
          <li>
            <label for="adminemail"><?php echo Filters::noXSS(L('fromaddress')); ?></label>
            <input id="adminemail" name="admin_email" class="text" type="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['admin_email']); ?>" />
          </li>
  
          <li>
            <label for="smtpserv"><?php echo Filters::noXSS(L('smtpserver')); ?></label>
            <input id="smtpserv" name="smtp_server" class="text" type="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_server']); ?>" />
            <?php if (extension_loaded('openssl')) : ?>
            <?php echo tpl_checkbox('email_ssl', $fs->prefs['email_ssl'], 'email_ssl'); ?> <label class="inline" for="email_ssl"><?php echo Filters::noXSS(L('ssl')); ?></label>
            <?php echo tpl_checkbox('email_tls', $fs->prefs['email_tls'], 'email_tls'); ?> <label class="inline" for="email_tls"><?php echo Filters::noXSS(L('tls')); ?></label>
            <?php endif; ?>
          </li>
  
          <li>
            <label for="smtpuser"><?php echo Filters::noXSS(L('smtpuser')); ?></label>
            <input id="smtpuser" name="smtp_user" class="text" type="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_user']); ?>" />
          </li>
  
          <li>
            <label for="smtppass"><?php echo Filters::noXSS(L('smtppass')); ?></label>
            <input id="smtppass" name="smtp_pass" class="text" type="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_pass']); ?>" />
          </li>
        </ul>
      </fieldset>
  
      <fieldset><legend><?php echo Filters::noXSS(L('jabbernotify')); ?></legend>
        <ul class="form_elements">
          <li>
            <label for="jabberserver"><?php echo Filters::noXSS(L('jabberserver')); ?></label>
            <input id="jabberserver" class="text" type="text" name="jabber_server" size="25" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_server']); ?>" />
            <?php if(extension_loaded('openssl')) : ?>
              <select id="jabber_ssl" name="jabber_ssl">
                <?php echo tpl_options(array('0' => L('none'), '1' => L('ssl'), '2' => L('tls')), $fs->prefs['jabber_ssl']); ?>

              </select>
              <label class="inline" for="jabber_ssl"><?php echo Filters::noXSS(L('ssl')); ?> / <?php echo Filters::noXSS(L('tls')); ?></label>
            <?php endif; ?>
          </li>
  
          <li>
            <label for="jabberport"><?php echo Filters::noXSS(L('jabberport')); ?></label>
            <input id="jabberport" class="text" type="text" name="jabber_port" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_port']); ?>" />
          </li>
  
          <li>
            <label for="jabberusername"><?php echo Filters::noXSS(L('jabberuser')); ?></label>
            <input id="jabberusername" class="text" type="text" name="jabber_username" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_username']); ?>" />
          </li>
  
          <li>
            <label for="jabberpassword"><?php echo Filters::noXSS(L('jabberpass')); ?></label>
            <input id="jabberpassword" name="jabber_password" class="text" type="text" size="40" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_password']); ?>" />
          </li>
        </ul>
      </fieldset>
    </div>

    <div id="lookandfeel" class="tab">

      <ul class="form_elements">
        <li>
          <label for="globaltheme"><?php echo Filters::noXSS(L('globaltheme')); ?></label>
          <select id="globaltheme" name="global_theme">
            <?php echo tpl_options(Flyspray::listThemes(), $fs->prefs['global_theme'], true); ?>

          </select>
        </li>
  
          <li>
            <label><?php echo Filters::noXSS(L('visiblecolumns')); ?></label>
            <!--<label id="viscollabel"><?php echo Filters::noXSS(L('visiblecolumns')); ?></label>-->
            <?php // Set the selectable column names
            $columnnames = array('id', 'project', 'tasktype', 'category', 'severity',
            'priority', 'summary', 'dateopened', 'status', 'openedby', 'private',
            'assignedto', 'lastedit', 'reportedin', 'dueversion', 'duedate',
            'comments', 'attachments', 'progress', 'dateclosed', 'os', 'votes');
            $selectedcolumns = explode(" ", $fs->prefs['visible_columns']);
            ?>
            <?php echo tpl_double_select('visible_columns', $columnnames, $selectedcolumns, true); ?>

          </li>
        </ul>
    </div>
    <div class="tbuttons">
      <input type="hidden" name="action" value="globaloptions" />
      <button type="submit"><?php echo Filters::noXSS(L('saveoptions')); ?></button>

      <button type="reset"><?php echo Filters::noXSS(L('resetoptions')); ?></button>
    </div>
  </form>
</div>
