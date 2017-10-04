<script type="text/javascript">
function ShowHidePassword(id) {
        if(document.getElementById(id).type=="text") {
            document.getElementById(id).type="password";
        } else {
            document.getElementById(id).type="text";
        }
}
</script>
<script>
    /*
    * Second argument is always the parent calling to deactivate not needed childs
    * Next args are all childsto be deactivated
    */
    function check_change(inverted)
    {
        var i;
        var parent = arguments[1];

        if(document.getElementById(parent).checked)
        {
            for (i = 2; i < arguments.length; i++)
            {
                if (inverted) {
                    document.getElementById(arguments[i]).checked = false;
                    document.getElementById(arguments[i]).disabled = true;
                }
                else {
                    document.getElementById(arguments[i]).checked = true;
                    document.getElementById(arguments[i]).disabled = false;
                }
            }
        }
        else
        {
            for (i = 2; i < arguments.length; i++)
            {
                if (inverted) {
                    document.getElementById(arguments[i]).disabled = false;
                }
                else {
                    document.getElementById(arguments[i]).disabled = true;
                }
            }
        }
    }
</script>
<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('admintoolboxlong')); ?> :: <?php echo Filters::noXSS(L('preferences')); ?></h3>
  <?php echo tpl_form(CreateURL('admin', 'prefs')); ?>
  <ul id="submenu">
   <li><a href="#general"><?php echo Filters::noXSS(L('general')); ?></a></li>
   <li><a href="#lookandfeel"><?php echo Filters::noXSS(L('lookandfeel')); ?></a></li>
   <li><a href="#userregistration"><?php echo Filters::noXSS(L('userregistration')); ?></a></li>
   <li><a href="#notifications"><?php echo Filters::noXSS(L('notifications')); ?></a></li>
  </ul>

   <div id="general" class="tab">
      <ul class="form_elements">
        <li>
          <label for="pagetitle"><?php echo Filters::noXSS(L('pagetitle')); ?></label>
          <input id="pagetitle" name="page_title" type="text" class="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['page_title']); ?>" />
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
          <label for="urlrewriting"><?php echo Filters::noXSS(L('urlrewriting')); ?></label>
          <select id="urlrewriting" name="url_rewriting">
            <?php echo tpl_options(array('1' => L('on'), '0' => L('off')), $fs->prefs['url_rewriting'], false); ?>
          </select>
        </li>

        <li>
          <label for="emailNoHTML"><?php echo Filters::noXSS(L('emailNoHTML')); ?></label>
        	<?php echo tpl_checkbox('emailNoHTML', $fs->prefs['emailNoHTML'], 'emailNoHTML'); ?>
        </li>

        <li>
          <?php
            // TODO WTF?? Isn't that an old temp fix?
            if (!array_key_exists('logo', $fs->prefs)) {
              $fs->prefs['logo'] = '';
            }
          ?>

          <label for="logo"><?php echo Filters::noXSS(L('showlogo')); ?></label>
          <?php if ($fs->prefs['logo']):?>
		    <img src="<?php echo Filters::noXSS($baseurl.'/'.$fs->prefs['logo']); ?>">
	      <?php endif ?>
        </li>

        <li>
          <label for="logo_input">&nbsp;</label>
          <input id="logo_input" name="logo" type="file" accept="image/*" value="<?php echo Filters::noXSS($fs->prefs['logo']); ?>" />
        </li>

        <li>
          <label for="enable_avatars"><?php echo Filters::noXSS(L('enableavatars')); ?></label>
          <?php echo tpl_checkbox('enable_avatars', $fs->prefs['enable_avatars'], 'enable_avatars', 1, array('onclick'=>'check_change(false, "enable_avatars", "gravatars", "max_avatar_size")')); ?>
        </li>

        <li>
          <label for="gravatars"><?php echo Filters::noXSS(L('showgravatars')); ?></label>
          <?php echo tpl_checkbox('gravatars', $fs->prefs['gravatars'], 'gravatars'); ?>
        </li>

        <li>
          <label for="max_avatar_size"><?php echo Filters::noXSS(L('maxavatarsize')); ?></label>
          <input id="max_avatar_size" name="max_avatar_size" type="text" class="text" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['max_avatar_size']); ?>" />
        </li>

        <li>
          <label for="hide_emails"><?php echo Filters::noXSS(L('hideemails')); ?></label>
        	<?php echo tpl_checkbox('hide_emails', $fs->prefs['hide_emails'], 'hide_emails'); ?>
        </li>

        <li>
          <label for="dateformat"><?php echo Filters::noXSS(L('dateformat')); ?></label>
          <select id="dateformat" name="dateformat">
            <?php echo tpl_date_formats($fs->prefs['dateformat']); ?>
          </select>
        </li>

        <li>
          <label for="dateformat_extended"><?php echo Filters::noXSS(L('dateformat_extended')); ?></label>
          <select id="dateformat_extended" name="dateformat_extended">
            <?php echo tpl_date_formats($fs->prefs['dateformat_extended'], true); ?>
          </select>
        </li>

        <li>
          <label for="cache_feeds"><?php echo Filters::noXSS(L('cache_feeds')); ?></label>
          <select id="cache_feeds" name="cache_feeds">
          <?php echo tpl_options(array('0' => L('no_cache'), '1' => L('cache_disk'), '2' => L('cache_db')), $fs->prefs['cache_feeds']); ?>
          </select>
        </li>

        <li>
          <label for="disable_lostpw"><?php echo Filters::noXSS(L('disable_lostpw')); ?></label>
          <?php echo tpl_checkbox('disable_lostpw', $fs->prefs['disable_lostpw'], 'disable_lostpw'); ?>

        </li>

        <li>
          <label for="disablechangepw"><?php echo Filters::noXSS(L('disable_changepw')); ?></label>
          <?php echo tpl_checkbox('disable_changepw', $fs->prefs['disable_changepw'], 'disablechangepw'); ?>
        </li>

        <li>
          <label for="days_before_alert"><?php echo Filters::noXSS(L('daysbeforealert')); ?></label>
          <input id="days_before_alert" name="days_before_alert" type="text" class="text" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['days_before_alert']); ?>" />
        </li>

        <li>
          <label for="max_vote_per_day"><?php echo Filters::noXSS(L('maxvoteperday')); ?></label>
          <input id="max_vote_per_day" name="max_vote_per_day" type="text" class="text" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['max_vote_per_day']); ?>" />
        </li>
        
        <li>
          <label for="votes_per_project"><?php echo Filters::noXSS(L('votesperproject')); ?></label>
          <input id="votes_per_project" name="votes_per_project" type="text" class="text" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['votes_per_project']); ?>" />
        </li>
        
        <li>
          <label class="labeltextarea"><?php echo Filters::noXSS(L('pageswelcomemsg')); ?></label>
          <?php
            $pages = array(
                'index' => L('tasklist'),
                'toplevel' => L('toplevel'),
                'reports' => L('reports'));
            $selectedPages = explode(' ', $fs->prefs['pages_welcome_msg']);
            echo tpl_double_select('pages_welcome_msg', $pages, $selectedPages, false, false);
          ?>
        </li>

        <li>
          <label class="labeltextarea" for="intromesg"><?php echo Filters::noXSS(L('mainmessage')); ?></label>
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
            <div class="hide preview" id="preview"></div>
            <button tabindex="9" type="button" onclick="showPreview('intromesg', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
          <?php endif; ?>
          <?php echo TextFormatter::textarea('intro_message', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg'), Post::val('intro_message', $fs->prefs['intro_message'])); ?>
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
          <label for="onlyoauthreg"><?php echo Filters::noXSS(L('onlyoauthreg')); ?></label>
          <?php echo tpl_checkbox('only_oauth_reg', $fs->prefs['only_oauth_reg'], 'onlyoauthreg', 1, array('onclick'=>'check_change(true, "onlyoauthreg", "needapproval", "spamproof")')); ?>
        </li>

        <li>
          <label for="needapproval"><?php echo Filters::noXSS(L('regapprovedbyadmin')); ?></label>
          <?php echo tpl_checkbox('need_approval', $fs->prefs['need_approval'], 'needapproval', 1, ($fs->prefs['only_oauth_reg']) ? array('disabled' => 'disabled', 'onclick' => 'check_change(true, "needapproval", "spamproof")') : array('onclick' => 'check_change("needapproval", "spamproof")')); ?>
        </li>
	
        <li>
          <label for="spamproof"><?php echo Filters::noXSS(L('spamproof')); ?></label>
          <?php echo tpl_checkbox('spam_proof', $fs->prefs['spam_proof'], 'spamproof', 1, ($fs->prefs['need_approval'] || $fs->prefs['only_oauth_reg'] ) ? array('disabled' => 'true') : ''); ?>
        </li>
	
	<li>
		<label for="captcha_securimage"><?php echo Filters::noXSS(L('regcaptcha')); ?></label>
		<?php echo tpl_checkbox('captcha_securimage', $fs->prefs['captcha_securimage'], 'captcha_securimage'); ?>
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

        <li>
          <label><?php echo Filters::noXSS(L('activeoauths')); ?></label>
          <?php
            $oauths = array('github', 'google', 'facebook', 'microsoft'/*, 'instagram', 'eventbrite', 'linkedin', 'vkontakte'*/); //TODO try the commented out for FS 1.1
            $selectedOauths = explode(' ', $fs->prefs['active_oauths']);
            echo tpl_double_select('active_oauths', $oauths, $selectedOauths, true, false);
          ?>
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
            <input id="adminemail" name="admin_email" class="text" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['admin_email']); ?>" />
          </li>

          <li>
            <label for="smtpserv"><?php echo Filters::noXSS(L('smtpserver')); ?></label>
            <input id="smtpserv" name="smtp_server" class="text" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_server']); ?>" />
            <?php if (extension_loaded('openssl')) : ?>
            <?php echo tpl_checkbox('email_ssl', $fs->prefs['email_ssl'], 'email_ssl'); ?> <label class="inline" for="email_ssl"><?php echo Filters::noXSS(L('ssl')); ?></label>
            <?php echo tpl_checkbox('email_tls', $fs->prefs['email_tls'], 'email_tls'); ?> <label class="inline" for="email_tls"><?php echo Filters::noXSS(L('tls')); ?></label>
            <?php endif; ?>
          </li>

          <li>
            <label for="smtpuser"><?php echo Filters::noXSS(L('smtpuser')); ?></label>
            <input id="smtpuser" name="smtp_user" class="text" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_user']); ?>" />
          </li>

          <li>
            <label for="smtppass"><?php echo Filters::noXSS(L('smtppass')); ?></label>
            <input id="smtppass" name="smtp_pass" class="text" type="password" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_pass']); ?>" />
          </li>
          <li>
              <label for="showsmtppass"><?php echo Filters::noXSS(L('showpass')); ?></label>
              <input id="showsmtppass" name="show_smtp_pass" class="text" type="checkbox"  onclick="ShowHidePassword('smtppass')"/>
          </li>
        </ul>
  <?php echo Filters::noXSS(L('testmailsettings')); ?>: <button onclick="testEmail();return false;"><?php echo Filters::noXSS(L('test')); ?></button><div id="emailresult" style="display:inline-block;"></div> <?php echo Filters::noXSS(L('testmailsettingsnotice')); ?>.
<script>
function testEmail(){
	var xmlHttp = new XMLHttpRequest();

	xmlHttp.onreadystatechange = function(){
		if(xmlHttp.readyState == 4){
			var target = document.getElementById('emailresult');
			if(xmlHttp.status == 200){
				if(xmlHttp.responseText=='ok'){
					target.style["background-color"]='#66ff00';
					target.innerHTML = '<i class="fa fa-check fa-2x"></i> '+xmlHttp.responseText;
				} else{
					target.innerHTML = '<i class="fa fa-warning fa-2x" style="color:#ff0"></i>' + xmlHttp.responseText;
					target.style["background-color"]='#ff6600';
				}
			} else{
				target.innerHTML = '<i class="fa fa-warning fa-2x" style="color:#ff0"></i>' + xmlHttp.responseText;
				target.style["background-color"]='#ff6600';
			}
		}
	}
	xmlHttp.open("POST", "<?php echo Filters::noXSS($baseurl); ?>js/callbacks/testemail.php", true);
	xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlHttp.send("name=email&csrftoken=<?php echo $_SESSION['csrftoken'] ?>");
}
</script>    
      </fieldset>

      <fieldset><legend><?php echo Filters::noXSS(L('jabbernotify')); ?></legend>
        <ul class="form_elements">
          <li>
            <label for="jabberserver"><?php echo Filters::noXSS(L('jabberserver')); ?></label>
            <input id="jabberserver" class="text" type="text" name="jabber_server" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_server']); ?>" />
            <?php if(extension_loaded('openssl')) : ?>
              <select id="jabber_ssl" name="jabber_ssl">
                <?php echo tpl_options(array('0' => L('none'), '1' => L('ssl'), '2' => L('tls')), $fs->prefs['jabber_ssl']); ?>
              </select>
              <label class="inline" for="jabber_ssl"><?php echo Filters::noXSS(L('ssl')); ?> / <?php echo Filters::noXSS(L('tls')); ?></label>
            <?php endif; ?>
          </li>

          <li>
            <label for="jabberport"><?php echo Filters::noXSS(L('jabberport')); ?></label>
            <input id="jabberport" class="text" type="text" name="jabber_port" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_port']); ?>" />
          </li>

          <li>
            <label for="jabberusername"><?php echo Filters::noXSS(L('jabberuser')); ?></label>
            <input id="jabberusername" class="text" type="text" name="jabber_username" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_username']); ?>" />
          </li>

          <li>
            <label for="jabberpassword"><?php echo Filters::noXSS(L('jabberpass')); ?></label>
            <input id="jabberpassword" name="jabber_password" class="text" type="password" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_password']); ?>" />
          </li>

          <li>
              <label for="showjabberpass"><?php echo Filters::noXSS(L('showpass')); ?></label>
              <input id="showjabberpass" name="show_jabber_pass" class="text" type="checkbox"  onclick="ShowHidePassword('jabberpassword')"/>
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
			<label for="customstyle" style="width:auto"><?php echo Filters::noXSS(L('customstyle')); ?></label>
			<select id="customstyle" name="custom_style">
			<?php
			$customs[]=array('', L('no'));
			$customstyles=glob_compat(BASEDIR ."/themes/".($proj->prefs['theme_style'])."/custom_*.css");
			foreach ($customstyles as $cs){
				$customs[]=array($cs,$cs);
			}
			echo tpl_options($customs, $proj->prefs['custom_style']);
			?>
			</select>
      </li>

        <?php // Set the selectable column names
            // Do NOT use real database column name here and in the next list,
            // but a term from translation table entries instead, because it's
            // also used elsewhere to draw a localized version of the name.
            // Look also at the end of function
            // tpl_draw_cell in scripts/index.php for further explanation.
            $columnnames = array(
                'id' => L('id'),
                'project' => L('project'),
                'parent' => L('parent'),
                'tasktype' => L('tasktype'),
                'category' => L('category'),
                'severity' => L('severity'),
                'priority' => L('priority'),
                'summary' => L('summary'),
                'dateopened' => L('dateopened'),
                'status' => L('status'),
                'openedby' => L('openedby'),
                'private' => L('private'),
                'assignedto' => L('assignedto'),
                'lastedit' => L('lastedit'),
                'editedby' => L('editedby'),
                'reportedin' => L('reportedin'),
                'dueversion' => L('dueversion'),
                'duedate' => L('duedate'),
                'comments' => L('comments'),
                'attachments' => L('attachments'),
                'progress' => L('progress'),
                'dateclosed' => L('dateclosed'),
                'closedby' => L('closedby'),
                'os' => L('os'),
                'votes' => L('votes'),
                'estimatedeffort' => L('estimatedeffort'),
                'effort' => L('effort'));
            $selectedcolumns = explode(' ', Post::val('visible_columns', $fs->prefs['visible_columns']));
        ?>

        <li>
          <label for="default_order_by"><?php echo Filters::noXSS(L('defaultorderby')); ?></label>
          <select id="default_order_by" name="default_order_by">
            <?php echo tpl_options($columnnames, $proj->prefs['sorting'][0]['field'], false); ?>
          </select>
          <select id="default_order_by_dir" name="default_order_by_dir">
            <?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), $proj->prefs['sorting'][0]['dir'], false); ?>
          </select>
        </li>
				<li>
          <label for="default_order_by2"><?php echo Filters::noXSS(L('defaultorderby2')); ?></label>
          <select id="default_order_by2" name="default_order_by2">
            <?php echo tpl_options($columnnames, $proj->prefs['sorting'][1]['field'], false); ?>
          </select>
          <select id="default_order_by_dir2" name="default_order_by_dir2">
            <?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), $proj->prefs['sorting'][1]['dir'], false); ?>
          </select>
        </li>

          <li>
            <label class="labeltextarea"><?php echo Filters::noXSS(L('visiblecolumns')); ?></label>
            <?php echo tpl_double_select('visible_columns', $columnnames, $selectedcolumns, false); ?>
          </li>

          <li>
            <label class="labeltextarea"><?php echo Filters::noXSS(L('visiblefields')); ?></label>
            <?php // Set the selectable field names
            $fieldnames = array(
                'parent' => L('parent'),
                'tasktype' => L('tasktype'),
                'category' => L('category'),
                'severity' => L('severity'),
                'priority' => L('priority'),
                'status' => L('status'),
                'private' => L('private'),
                'assignedto' => L('assignedto'),
                'reportedin' => L('reportedin'),
                'dueversion' => L('dueversion'),
                'duedate' => L('duedate'),
                'progress' => L('progress'),
                'os' => L('os'),
                'votes' => L('votes'));
            $selectedfields = explode(' ', Post::val('visible_fields', $fs->prefs['visible_fields']));
            echo tpl_double_select('visible_fields', $fieldnames, $selectedfields, false);
            ?>
          </li>

	<?php if(isset($fs->prefs['general_integration'])): ?>
	<li>
	<label class="labeltextarea"><?php echo Filters::noXSS(L('generalintegration')); ?></label>
	<?php echo TextFormatter::textarea('general_integration', 8, 70, array('id'=>'general_integration'), Post::val('general_integration', $fs->prefs['general_integration'])); ?>
	</li>
	<?php endif; ?>

	<?php if(isset($fs->prefs['footer_integration'])): ?>
	<li>
	<label class="labeltextarea"><?php echo Filters::noXSS(L('footerintegration')); ?></label>
	<?php echo TextFormatter::textarea('footer_integration', 8, 70, array('id'=>'footer_integration'), Post::val('footer_integration', $fs->prefs['footer_integration'])); ?>
	</li>
	<?php endif; ?>

	</ul>
    </div>
    <div class="tbuttons">
      <input type="hidden" name="action" value="globaloptions" />
      <button type="submit" class="positive"><?php echo Filters::noXSS(L('saveoptions')); ?></button>
      <button type="reset"><?php echo Filters::noXSS(L('resetoptions')); ?></button>
    </div>
  </form>
</div>
