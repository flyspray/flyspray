<div id="toolbox">
  <h3>{L('admintoolboxlong')} :: {L('preferences')}</h3>

  <form action="{CreateURL('admin', 'prefs')}" method="post">
  <ul id="submenu">
   <li><a href="#general">{L('general')}</a></li>
   <li><a href="#userregistration">{L('userregistration')}</a></li>
   <li><a href="#notifications">{L('notifications')}</a></li>
   <li><a href="#lookandfeel">{L('lookandfeel')}</a></li>
  </ul>
  
   <div id="general" class="tab">
      <ul class="form_elements">
        <li>
          <label for="pagetitle">{L('pagetitle')}</label>
          <input id="pagetitle" name="page_title" type="text" class="text" size="40" maxlength="100" value="{$fs->prefs['page_title']}" />
        </li>   

        <li>
          <label for="defaultproject">{L('defaultproject')}</label>
          <select id="defaultproject" name="default_project">
            {!tpl_options(array_merge(array(0 => L('allprojects')), Flyspray::listProjects()), $fs->prefs['default_project'])}
          </select>
        </li>   

        <li>
          <label for="langcode">{L('language')}</label>
          <select id="langcode" name="lang_code">
            {!tpl_options(Flyspray::listLangs(), $fs->prefs['lang_code'], true)}
          </select>
        </li>   

        <li>
          <label for="dateformat">{L('dateformat')}</label>
          <input id="dateformat" name="dateformat" type="text" class="text" size="40" maxlength="30" value="{$fs->prefs['dateformat']}" />
        </li>   

        <li>
          <label for="dateformat_extended">{L('dateformat_extended')}</label>
          <input id="dateformat_extended" name="dateformat_extended" class="text" type="text" size="40" maxlength="30" value="{$fs->prefs['dateformat_extended']}" />
        </li>   

        <li>
          <label for="cache_feeds">{L('cache_feeds')}</label>
          <select id="cache_feeds" name="cache_feeds">
          {!tpl_options(array('0' => L('no_cache'), '1' => L('cache_disk'), '2' => L('cache_db')), $fs->prefs['cache_feeds'])}
          </select>
        </li>   
      </ul>
    </div>

    <div id="userregistration" class="tab">
      <ul class="form_elements">
        <li>
          <label for="allowusersignups">{L('anonreg')}</label>
          {!tpl_checkbox('anon_reg', $fs->prefs['anon_reg'], 'allowusersignups')}
        </li>   

        <li>
          <label for="spamproof">{L('spamproof')}</label>
          {!tpl_checkbox('spam_proof', $fs->prefs['spam_proof'], 'spamproof')}
        </li>   

        <li>
          <label for="notify_registration">{L('notify_registration')}</label>
          {!tpl_checkbox('notify_registration', $fs->prefs['notify_registration'], 'notify_registration')}
        </li>   

        <li>
          <label for="defaultglobalgroup">{L('defaultglobalgroup')}</label>
          <select id="defaultglobalgroup" name="anon_group">
            {!tpl_options(Flyspray::listGroups(), $fs->prefs['anon_group'])}
          </select>
        </li>   
      </ul>
    </div>

    <div id="notifications" class="tab">
      <ul class="form_elements">
        <li>
          <label for="usernotify">{L('forcenotify')}</label>
          <select id="usernotify" name="user_notify">
            {!tpl_options(array(L('neversend'), L('userchoose'), L('email'), L('jabber')), $fs->prefs['user_notify'])}
          </select>
        </li>
      </ul>
      
      <fieldset><legend>{L('emailnotify')}</legend>
        <ul class="form_elements">
          <li>
            <label for="adminemail">{L('fromaddress')}</label>
            <input id="adminemail" name="admin_email" class="text" type="text" size="40" maxlength="100" value="{$fs->prefs['admin_email']}" />
          </li>
  
          <li>
            <label for="smtpserv">{L('smtpserver')}</label>
            <input id="smtpserv" name="smtp_server" class="text" type="text" size="40" maxlength="100" value="{$fs->prefs['smtp_server']}" />
            <?php if (extension_loaded('openssl')) : ?>
            {!tpl_checkbox('email_ssl', $fs->prefs['email_ssl'], 'email_ssl')} <label class="inline" for="email_ssl">{L('ssl')}</label>
            {!tpl_checkbox('email_tls', $fs->prefs['email_tls'], 'email_tls')} <label class="inline" for="email_tls">{L('tls')}</label>
            <?php endif; ?>
          </li>
  
          <li>
            <label for="smtpuser">{L('smtpuser')}</label>
            <input id="smtpuser" name="smtp_user" class="text" type="text" size="40" maxlength="100" value="{$fs->prefs['smtp_user']}" />
          </li>
  
          <li>
            <label for="smtppass">{L('smtppass')}</label>
            <input id="smtppass" name="smtp_pass" class="text" type="text" size="40" maxlength="100" value="{$fs->prefs['smtp_pass']}" />
          </li>
        </ul>
      </fieldset>
  
      <fieldset><legend>{L('jabbernotify')}</legend>
        <ul class="form_elements">
          <li>
            <label for="jabberserver">{L('jabberserver')}</label>
            <input id="jabberserver" class="text" type="text" name="jabber_server" size="25" maxlength="100" value="{$fs->prefs['jabber_server']}" />
                <?php if(extension_loaded('openssl')) : ?>
            {!tpl_checkbox('jabber_ssl', $fs->prefs['jabber_ssl'], 'jabber_ssl')} <label class="inline" for="jabber_ssl">{L('ssl')}</label>
                    <?php endif; ?>
          </li>
  
          <li>
            <label for="jabberport">{L('jabberport')}</label>
            <input id="jabberport" class="text" type="text" name="jabber_port" size="40" maxlength="100" value="{$fs->prefs['jabber_port']}" />
          </li>
  
          <li>
            <label for="jabberusername">{L('jabberuser')}</label>
            <input id="jabberusername" class="text" type="text" name="jabber_username" size="40" maxlength="100" value="{$fs->prefs['jabber_username']}" />
          </li>
  
          <li>
            <label for="jabberpassword">{L('jabberpass')}</label>
            <input id="jabberpassword" name="jabber_password" class="text" type="text" size="40" maxlength="100" value="{$fs->prefs['jabber_password']}" />
          </li>
        </ul>
      </fieldset>
    </div>

    <div id="lookandfeel" class="tab">

      <ul class="form_elements">
        <li>
          <label for="globaltheme">{L('globaltheme')}</label>
          <select id="globaltheme" name="global_theme">
            {!tpl_options(Flyspray::listThemes(), $fs->prefs['global_theme'], true)}
          </select>
        </li>
  
          <li>
            <label>{L('visiblecolumns')}</label>
            <!--<label id="viscollabel">{L('visiblecolumns')}</label>-->
            <?php // Set the selectable column names
            $columnnames = array('id', 'project', 'tasktype', 'category', 'severity',
            'priority', 'summary', 'dateopened', 'status', 'openedby', 'private',
            'assignedto', 'lastedit', 'reportedin', 'dueversion', 'duedate',
            'comments', 'attachments', 'progress', 'dateclosed', 'os', 'votes');
            $selectedcolumns = explode(" ", $fs->prefs['visible_columns']);
            ?>
            {!tpl_double_select('visible_columns', $columnnames, $selectedcolumns, true)}
          </li>
        </ul>
    </div>
    <div class="tbuttons">
      <input type="hidden" name="action" value="globaloptions" />
      <button type="submit">{L('saveoptions')}</button>

      <button type="reset">{L('resetoptions')}</button>
    </div>
  </form>
</div>
