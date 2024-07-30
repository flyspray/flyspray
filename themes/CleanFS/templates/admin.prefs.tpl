<script type="text/javascript">
function ShowHidePassword(id) {
	if(document.getElementById(id).type=="text") {
		document.getElementById(id).type="password";
	} else {
		document.getElementById(id).type="text";
	}
}

/*
 * Second argument is always the parent calling to deactivate not needed childs
 * Next args are all childsto be deactivated
*/
function check_change(inverted) {
	var i;
	var parent = arguments[1];

	if(document.getElementById(parent).checked) {
		for (i = 2; i < arguments.length; i++) {
			if (inverted) {
				document.getElementById(arguments[i]).checked = false;
				document.getElementById(arguments[i]).disabled = true;
			} else {
				document.getElementById(arguments[i]).checked = true;
				document.getElementById(arguments[i]).disabled = false;
			}
		}
	} else {
		for (i = 2; i < arguments.length; i++) {
			if (inverted) {
				document.getElementById(arguments[i]).disabled = false;
			} else {
				document.getElementById(arguments[i]).disabled = true;
			}
		}
	}
}

function testEmail(){
	var xmlHttp = new XMLHttpRequest();

	xmlHttp.onreadystatechange = function(){
		if(xmlHttp.readyState == 4){
			var target = document.getElementById('emailresult');
			target.style['vertical-align']='middle';
			target.style['border-radius']='3px';
			target.style['padding']='3px';

			if(xmlHttp.status == 200){
				if(xmlHttp.responseText=='ok'){
					target.style["background-color"]='#ccffcc';
					target.style["border"]='1px solid #090';
					target.innerHTML = '<span class="fas fa-circle-check fa-lg" style="color: #090;margin-right:.35em;"></span> '+xmlHttp.responseText;
				} else{
					target.style["background-color"]='#ffe0cc';
					target.style["border"]='1px solid #ff6600';
					target.innerHTML = '<span class="fas fa-triangle-exclamation fa-lg" style="color:#ff6600;margin-right:.35em;"></span>' + xmlHttp.responseText;
				}
			} else{
				target.style["background-color"]='#ffe0cc';
				target.style["border"]='1px solid #ff6600';
				target.innerHTML = '<span class="fas fa-triangle-exclamation fa-lg" style="color:#ff6600;margin-right:.35em;"></span>' + xmlHttp.responseText;
			}
		}
	}
	xmlHttp.open("POST", "<?php echo Filters::noXSS($baseurl); ?>js/callbacks/testemail.php", true);
	xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlHttp.send("name=email&csrftoken=<?php echo $_SESSION['csrftoken'] ?>");
}

</script>
<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h2><?= eL('admintoolboxlong') ?> :: <?= eL('preferences') ?></h2>
<?php echo tpl_form(createURL('admin', 'prefs')); ?>
<ul id="submenu">
	<li><a href="#general"><span class="fas fa-sliders"></span><span><?= eL('general') ?></span></a></li>
	<li><a href="#lookandfeel"><span class="fas fa-eye"></span><span><?= eL('lookandfeel') ?></span></a></li>
	<li><a href="#userregistration"><span class="fas fa-user-plus"></span><span><?= eL('userregistration') ?></span></a></li>
	<li><a href="#notifications"><span class="fas fa-bell"></span><span><?= eL('notifications') ?></span></a></li>
	<li><a href="#antispam"><span class="fas fa-envelopes-bulk"></span><span><?= eL('antispam') ?></span></a></li>
</ul>

<div id="general" class="tab">
	<ul class="form_elements">
		<li>
			<label for="pagetitle"><?= eL('pagetitle') ?></label>
			<div class="valuewrap">
				<input id="pagetitle" name="page_title" type="text" class="text fi-x-large" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['page_title']); ?>" />
			</div>
		</li>
		<li>
			<label for="defaultproject"><?= eL('defaultproject') ?></label>
			<div class="valuewrap">
				<select id="defaultproject" name="default_project">
				<?php echo tpl_options(array_merge(array(0 => L('allprojects')), Flyspray::listProjects()), $fs->prefs['default_project']); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="langcode"><?= eL('language') ?></label>
			<div class="valuewrap">
				<select id="langcode" name="lang_code">
				<?php echo tpl_options(Flyspray::listLangs(), $fs->prefs['lang_code'], true); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="urlrewriting"><?= eL('urlrewriting') ?></label>
			<div class="valuewrap">
				<select id="urlrewriting" name="url_rewriting">
				<?php echo tpl_options(array('1' => L('on'), '0' => L('off')), $fs->prefs['url_rewriting'], false); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="emailNoHTML"><?= eL('emailNoHTML') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('emailNoHTML', $fs->prefs['emailNoHTML'], 'emailNoHTML'); ?>
			</div>
		</li>
		<li>
<?php
	// TODO WTF?? Isn't that an old temp fix?
	if (!array_key_exists('logo', $fs->prefs)) {
		$fs->prefs['logo'] = '';
	}
?>
			<label for="prefslogo"><?= eL('showlogo') ?></label>
			<div class="valuewrap">
			<?php if ($fs->prefs['logo']):?>
				<img src="<?php echo Filters::noXSS($baseurl.'/'.$fs->prefs['logo']); ?>" id="prefslogo">
			<?php endif ?>
			</div>
		</li>
		<li>
			<label for="logo_input">&nbsp;</label>
			<div class="valuewrap">
				<input id="logo_input" name="logo" type="file" accept="image/*" value="<?php echo Filters::noXSS($fs->prefs['logo']); ?>" />
			</div>
		</li>
		<li>
			<label for="massops"><?= eL('massopsenable') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('massops', $fs->prefs['massops'], 'massops'); ?>
			</div>
		</li>
		<li>
			<label for="enable_avatars"><?= eL('enableavatars') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('enable_avatars', $fs->prefs['enable_avatars'], 'enable_avatars', 1, array('onclick'=>'check_change(false, "enable_avatars", "gravatars", "max_avatar_size")')); ?>
			</div>
		</li>
		<li>
			<label for="gravatars"><?= eL('showgravatars') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('gravatars', $fs->prefs['gravatars'], 'gravatars'); ?>
			</div>
		</li>
		<li>
			<label for="max_avatar_size"><?= eL('maxavatarsize') ?></label>
			<div class="valuewrap">
				<input id="max_avatar_size" name="max_avatar_size" type="text" class="text fi-xx-small ta-e" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['max_avatar_size']); ?>" />
			</div>
		</li>
		<li>
			<label for="hide_emails"><?= eL('hideemails') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('hide_emails', $fs->prefs['hide_emails'], 'hide_emails'); ?>
			</div>
		</li>
		<li>
			<label for="dateformat"><?= eL('dateformat') ?></label>
			<div class="valuewrap">
				<select id="dateformat" name="dateformat">
				<?php echo tpl_date_formats($fs->prefs['dateformat']); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="dateformat_extended"><?= eL('dateformat_extended') ?></label>
			<div class="valuewrap">
				<select id="dateformat_extended" name="dateformat_extended">
				<?php echo tpl_date_formats($fs->prefs['dateformat_extended'], true); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="cache_feeds"><?= eL('cache_feeds') ?></label>
			<div class="valuewrap">
				<select id="cache_feeds" name="cache_feeds">
				<?php echo tpl_options(array('0' => L('no_cache'), '1' => L('cache_disk'), '2' => L('cache_db')), $fs->prefs['cache_feeds']); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="disable_lostpw"><?= eL('disable_lostpw') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('disable_lostpw', $fs->prefs['disable_lostpw'], 'disable_lostpw'); ?>
			</div>
		</li>
		<li>
			<label for="disablechangepw"><?= eL('disable_changepw') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('disable_changepw', $fs->prefs['disable_changepw'], 'disablechangepw'); ?>
			</div>
		</li>
		<li>
			<label for="days_before_alert"><?= eL('daysbeforealert') ?></label>
			<div class="valuewrap">
				<input id="days_before_alert" name="days_before_alert" type="text" class="text fi-xx-small ta-e" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['days_before_alert']); ?>" />
			</div>
		</li>
		<li>
			<label for="max_vote_per_day"><?= eL('maxvoteperday') ?></label>
			<div class="valuewrap">
				<input id="max_vote_per_day" name="max_vote_per_day" type="text" class="text fi-xx-small ta-e" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['max_vote_per_day']); ?>" />
			</div>
		</li>
		<li>
			<label for="votes_per_project"><?= eL('votesperproject') ?></label>
			<div class="valuewrap">
				<input id="votes_per_project" name="votes_per_project" type="text" class="text fi-xx-small ta-e" size="3" maxlength="3" value="<?php echo Filters::noXSS($fs->prefs['votes_per_project']); ?>" />
			</div>
		</li>
		<li class="wide-element">
			<label class="labeltextarea"><?= eL('pageswelcomemsg') ?></label>
			<div class="valuewrap">
			<?php
				$pages = array(
					'index' => L('tasklist'),
					'toplevel' => L('toplevel'),
					'reports' => L('reports'));
				$selectedPages = explode(' ', $fs->prefs['pages_welcome_msg']);
				echo tpl_double_select('pages_welcome_msg', $pages, $selectedPages, false, false);
			?>
			</div>
		</li>
		<li class="wide-element">
			<label class="labeltextarea" for="intromesg"><?= eL('mainmessage') ?></label>
			<div class="valuewrap">
				<div class="richtextwrap">
				<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
					<div class="hide preview" id="preview"></div>
					<button tabindex="9" type="button" onclick="showPreview('intromesg', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
				<?php endif; ?>
				<?php echo TextFormatter::textarea('intro_message', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg', 'class' => 'richtext txta-large'), Post::val('intro_message', $fs->prefs['intro_message'])); ?>
				</div>
			</div>
		</li>
	</ul>
</div>

<div id="userregistration" class="tab">
	<ul class="form_elements">
		<li>
			<label for="allowusersignups"><?= L('anonreg') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('anon_reg', $fs->prefs['anon_reg'], 'allowusersignups'); ?>
			</div>
		</li>
		<li>
			<label for="onlyoauthreg"><?= eL('onlyoauthreg') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('only_oauth_reg', $fs->prefs['only_oauth_reg'], 'onlyoauthreg', 1, array('onclick'=>'check_change(true, "onlyoauthreg", "needapproval", "spamproof")')); ?>
			</div>
		</li>
		<li>
			<label for="needapproval"><?= eL('regapprovedbyadmin') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('need_approval', $fs->prefs['need_approval'], 'needapproval', 1, ($fs->prefs['only_oauth_reg']) ? array('disabled' => 'disabled', 'onclick' => 'check_change(true, "needapproval", "spamproof")') : array('onclick' => 'check_change("needapproval", "spamproof")')); ?>
			</div>
		</li>
		<li><?php /* TODO rename misleading 'spamproof' pref to something like email_verify */ ?>
			<label for="spamproof"><?= eL('spamproof') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('spam_proof', $fs->prefs['spam_proof'], 'spamproof', 1, ($fs->prefs['need_approval'] || $fs->prefs['only_oauth_reg'] ) ? array('disabled' => 'true') : ''); ?>
			</div>
		</li>
		<li>
			<label for="repeat_password"><?= eL('repeatpassword') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('repeat_password', $fs->prefs['repeat_password'], 'repeat_password'); ?>
			</div>
		</li>
		<li>
			<label for="repeat_emailaddress"><?= eL('repeatemailaddress') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('repeat_emailaddress', $fs->prefs['repeat_emailaddress'], 'repeat_emailaddress'); ?>
			</div>
		</li>
		<li>
			<label for="notify_registration"><?= eL('notify_registration') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('notify_registration', $fs->prefs['notify_registration'], 'notify_registration'); ?>
			</div>
		</li>
		<li>
			<label for="defaultglobalgroup"><?= eL('defaultglobalgroup') ?></label>
			<div class="valuewrap">
				<select id="defaultglobalgroup" name="anon_group">
				<?php echo tpl_options(Flyspray::listGroups(), $fs->prefs['anon_group']); ?>
				</select>
			</div>
		</li>
		<li class="wide-element">
			<label><?= eL('activeoauths') ?></label>
			<div class="valuewrap">
			<?php
				$oauths = array(
					'github',
					'google',
					'facebook',
					'microsoft'
					/*, 'instagram', 'eventbrite', 'linkedin', 'vkontakte'*/
				); //TODO try the commented out for FS 1.1

				$selectedOauths = explode(' ', $fs->prefs['active_oauths']);
				echo tpl_double_select('active_oauths', $oauths, $selectedOauths, true, false);
			?>
			</div>
		</li>
	</ul>
</div>

<div id="antispam" class="tab">
	<h3><?= eL('antispam') ?></h3>
	<p><?= eL('antispamprefsinfo') ?></p>

	<ul class="form_elements">
		<li>
			<label for="relnofollow"><?= eL('relnofollow') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('relnofollow', isset($fs->prefs['relnofollow']) ? $fs->prefs['relnofollow'] : false, 'relnofollow'); ?>
			</div>
		</li>
	</ul>

	<h4>Securimage</h4>
	<p><?= eL('securimageprefsinfo') ?></p>
	<ul class="form_elements">
		<li>
			<label for="captcha_securimage"><?= eL('securimageenable') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('captcha_securimage', isset($fs->prefs['captcha_securimage']) ? $fs->prefs['captcha_securimage']:false, 'captcha_securimage'); ?>
			</div>
		</li>
	</ul>

	<h4>Google reCaptcha</h4>
	<p><?= eL('recaptchaprefsinfo') ?></p>
	<ul class="form_elements">
		<li>
			<label for="captcha_recaptcha"><?= eL('recaptchaenable') ?></label>
			<div class="valuewrap">
				<?php echo tpl_checkbox('captcha_recaptcha', isset($fs->prefs['captcha_recaptcha']) ? $fs->prefs['captcha_recaptcha']:false, 'captcha_recaptcha'); ?>
			</div>
		</li>
		<li class="recaptchaconf">
			<label for="captcha_recaptcha_sitekey">sitekey</label>
			<div class="valuewrap">
				<input id="captcha_recaptcha_sitekey" class="text fi-large" type="text" name="captcha_recaptcha_sitekey" value="<?php echo Filters::noXSS(isset($fs->prefs['captcha_recaptcha_sitekey']) ? $fs->prefs['captcha_recaptcha_sitekey']:''); ?>" />
			</div>
		</li>
		<li class="recaptchaconf">
			<label for="captcha_recaptcha_secret">secret</label>
			<div class="valuewrap">
				<input id="captcha_recaptcha_secret" class="text fi-large" type="text" name="captcha_recaptcha_secret" value="<?php echo Filters::noXSS(isset($fs->prefs['captcha_recaptcha_secret']) ? $fs->prefs['captcha_recaptcha_secret']:''); ?>" />
			</div>
		</li>
	</ul>
</div>

<div id="notifications" class="tab">
	<ul class="form_elements">
		<li>
			<label for="usernotify"><?php echo Filters::noXSS(L('forcenotify')); ?></label>
			<div class="valuewrap">
				<select id="usernotify" name="user_notify">
				<?php echo tpl_options(array(L('neversend'), L('userchoose'), L('email'), L('jabber')), $fs->prefs['user_notify']); ?>
				</select>
			</div>
		</li>
	</ul>

	<fieldset>
		<legend><?php echo Filters::noXSS(L('emailnotify')); ?></legend>
		<ul class="form_elements">
			<li>
				<label for="adminemail"><?php echo Filters::noXSS(L('fromaddress')); ?></label>
				<div class="valuewrap">
					<input id="adminemail" name="admin_email" class="text fi-large" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['admin_email']); ?>" />
				</div>
			</li>
			<li>
				<label for="smtpserv"><?php echo Filters::noXSS(L('smtpserver')); ?></label>
				<div class="valuewrap">
					<div class="valuemulti">
						<input id="smtpserv" name="smtp_server" class="text fi-large" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_server']); ?>" />
					<?php if (extension_loaded('openssl')) : ?>
						<div class="valuemultipair">
							<?php echo tpl_checkbox('email_ssl', $fs->prefs['email_ssl'], 'email_ssl'); ?>
							<label class="inline" for="email_ssl"><?php echo Filters::noXSS(L('ssl')); ?></label>
						</div>
						<div class="valuemultipair">
							<?php echo tpl_checkbox('email_tls', $fs->prefs['email_tls'], 'email_tls'); ?>
							<label class="inline" for="email_tls"><?php echo Filters::noXSS(L('tls')); ?></label>
						</div>
					<?php endif; ?>
					</div>
				</div>
			</li>
			<li>
				<label for="smtpuser"><?php echo Filters::noXSS(L('smtpuser')); ?></label>
				<div class="valuewrap">
					<input id="smtpuser" name="smtp_user" class="text fi-medium" type="text" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_user']); ?>" />
				</div>
			</li>
			<li>
				<label for="smtppass"><?php echo Filters::noXSS(L('smtppass')); ?></label>
				<div class="valuewrap">
					<input id="smtppass" name="smtp_pass" class="text fi-medium" type="password" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['smtp_pass']); ?>" />
				</div>
			</li>
			<li>
				<label for="showsmtppass"><?php echo Filters::noXSS(L('showpass')); ?></label>
				<div class="valuewrap">
					<input id="showsmtppass" name="show_smtp_pass" class="text" type="checkbox"  onclick="ShowHidePassword('smtppass')"/>
				</div>
			</li>
		</ul>

		<p>
			<?php echo Filters::noXSS(L('testmailsettings')); ?>: <button onclick="testEmail();return false;"><?php echo Filters::noXSS(L('test')); ?></button>
			<span id="emailresult" style="display:inline-block;"></span>
		</p>
		<p><?php echo Filters::noXSS(L('testmailsettingsnotice')); ?>.</p>
	</fieldset>

	<fieldset><legend><?php echo Filters::noXSS(L('jabbernotify')); ?></legend>
		<ul class="form_elements">
			<li>
				<label for="jabberserver"><?php echo Filters::noXSS(L('jabberserver')); ?></label>
				<div class="valuewrap">
					<div class="valuemulti">
						<input id="jabberserver" class="text fi-large" type="text" name="jabber_server" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_server']); ?>" />
						<div class="valuemultipair">
						<?php if(extension_loaded('openssl')) : ?>
							<label for="jabber_ssl"><?php echo Filters::noXSS(L('ssl')); ?> / <?php echo Filters::noXSS(L('tls')); ?></label>
							<select id="jabber_ssl" name="jabber_ssl">
							<?php echo tpl_options(array('0' => L('none'), '1' => L('ssl'), '2' => L('tls')), $fs->prefs['jabber_ssl']); ?>
							</select>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</li>
			<li>
				<label for="jabberport"><?php echo Filters::noXSS(L('jabberport')); ?></label>
				<div class="valuewrap">
					<input id="jabberport" class="text fi-xx-small ta-e" type="text" name="jabber_port" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_port']); ?>" />
				</div>
			</li>

			<li>
				<label for="jabberusername"><?php echo Filters::noXSS(L('jabberuser')); ?></label>
				<div class="valuewrap">
					<input id="jabberusername" class="text fi-medium" type="text" name="jabber_username" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_username']); ?>" />
				</div>
			</li>
			<li>
				<label for="jabberpassword"><?php echo Filters::noXSS(L('jabberpass')); ?></label>
				<div class="valuewrap">
					<input id="jabberpassword" name="jabber_password" class="text fi-medium" type="password" maxlength="100" value="<?php echo Filters::noXSS($fs->prefs['jabber_password']); ?>" />
				</div>
			</li>
			<li>
				<label for="showjabberpass"><?php echo Filters::noXSS(L('showpass')); ?></label>
				<div class="valuewrap">
					<input id="showjabberpass" name="show_jabber_pass" class="text" type="checkbox"  onclick="ShowHidePassword('jabberpassword')"/>
				</div>
			</li>
		</ul>
	</fieldset>
</div>

<div id="lookandfeel" class="tab">
	<ul class="form_elements">
		<li>
			<label for="globaltheme"><?php echo Filters::noXSS(L('globaltheme')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<select id="globaltheme" name="global_theme">
					<?php echo tpl_options(Flyspray::listThemes(), $fs->prefs['global_theme'], true); ?>
					</select>
					<div class="valuemultipair">
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
					</div>
				</div>
			</div>
		</li>
		<li>
			<label for="default_entry"><?php echo Filters::noXSS(L('defaultentry')); ?></label>
			<div class="valuewrap">
				<select id="default_entry" name="default_entry">
				<?php echo tpl_options(array('index' => L('tasklist'),'toplevel' => L('toplevel')), Post::val('default_entry', $proj->prefs['default_entry'])); ?>
				</select>
			</div>
		</li>

		<?php
			// Set the selectable column names
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
			<div class="valuewrap">
				<div class="valuemulti">
					<select id="default_order_by" name="default_order_by">
					<?php echo tpl_options($columnnames, $proj->prefs['sorting'][0]['field'], false); ?>
					</select>
					<select id="default_order_by_dir" name="default_order_by_dir">
					<?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), $proj->prefs['sorting'][0]['dir'], false); ?>
					</select>
				</div>
			</div>
		</li>
		<li>
			<label for="default_order_by2"><?php echo Filters::noXSS(L('defaultorderby2')); ?></label>
			<div class="valuewrap">
				<div class="valuemulti">
					<select id="default_order_by2" name="default_order_by2">
					<?php echo tpl_options($columnnames, isset($proj->prefs['sorting'][1]['field']) ? $proj->prefs['sorting'][1]['field'] : null, false); ?>
					</select>
					<select id="default_order_by_dir2" name="default_order_by_dir2">
					<?php echo tpl_options(array('asc' => L('ascending'), 'desc' => L('descending')), isset($proj->prefs['sorting'][1]['dir']) ? $proj->prefs['sorting'][1]['dir'] : null, false); ?>
					</select>
				</div>
			</div>
		</li>
		<li class="wide-element">
			<label><?php echo Filters::noXSS(L('visiblecolumns')); ?></label>
			<div class="valuewrap">
				<?php echo tpl_double_select('visible_columns', $columnnames, $selectedcolumns, false); ?>
			</div>
		</li>
		<li class="wide-element">
			<label><?php echo Filters::noXSS(L('visiblefields')); ?></label>
			<div class="valuewrap">
				<div class="richtextwrap">
				<?php
					// Set the selectable field names
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
				</div>
			</div>
		</li>
	<?php if(isset($fs->prefs['general_integration'])): ?>
		<li class="wide-element">
			<label class="labeltextarea"><?php echo Filters::noXSS(L('generalintegration')); ?></label>
			<div class="valuewrap">
				<div class="richtextwrap">
				<?php echo TextFormatter::textarea('general_integration', 8, 70, array('id'=>'general_integration', 'class' => 'richtext txta-large'), Post::val('general_integration', $fs->prefs['general_integration'])); ?>
				</div>
			</div>
		</li>
	<?php endif; ?>

	<?php if(isset($fs->prefs['footer_integration'])): ?>
		<li class="wide-element">
			<label class="labeltextarea"><?php echo Filters::noXSS(L('footerintegration')); ?></label>
			<div class="valuewrap">
				<div class="richtextwrap">
				<?php echo TextFormatter::textarea('footer_integration', 8, 70, array('id'=>'footer_integration', 'class' => 'richtext txta-large'), Post::val('footer_integration', $fs->prefs['footer_integration'])); ?>
				</div>
			</div>
		</li>
	<?php endif; ?>
	</ul>
</div>

<div class="buttons">
	<input type="hidden" name="action" value="globaloptions" />
	<button type="submit" class="positive"><?php echo Filters::noXSS(L('saveoptions')); ?></button>
	<button type="reset"><?php echo Filters::noXSS(L('resetoptions')); ?></button>
</div>
</form>
</div>

