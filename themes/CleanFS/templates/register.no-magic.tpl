<div class="box">
	<h2><?= eL('registernewuser') ?></h2>
<?php echo tpl_form(Filters::noXSS(createUrl('register')),null,null,null,'id="registernewuser"'); ?>
<style>
	#captcha_code{width:100px;}
	.captchali .securimage label{width:auto;}
	.captchali .securimage {display:inline-block; width:300px;}
</style>

	<ul class="form_elements">
		<li class="required">
			<label for="username"><?= eL('username') ?></label>
			<div class="valuewrap">
				<input required="required" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" id="username" name="user_name" type="text" size="20" maxlength="32" class="fi-medium" onblur="checkname(this.value);" />
				<span class="note"><?= eL('validusername') ?></span>
				<strong><span id="errormessage"></span></strong>
			</div>
		</li>

		<li class="required">
			<label for="realname"><?= eL('realname') ?></label>
			<div class="valuewrap">
				<input required="required" value="<?php echo Filters::noXSS(Req::val('real_name')); ?>" id="realname" name="real_name" type="text" size="30" maxlength="100" class="fi-medium" />
			</div>
		</li>

		<li class="required">
			<label for="emailaddress"><?= eL('emailaddress') ?></label>
			<div class="valuewrap">
				<input id="emailaddress" value="<?php echo Filters::noXSS(Req::val('email_address')); ?>" name="email_address" required="required" type="text" size="20" maxlength="100" class="fi-medium" />
<?php
			/* multiple email addresses now disabled at registration, so do not show that multiple addresses hint anymore.
			 * 1. Would require separate email verification (roundtrip with magic verification code) for each email address.
			 * 2. Users need a user interface to manage their multiple email addresses and which action/notification types connected to each address.
			 */
			/* eL('validemail') */
?>
			</div>
		</li>
		<?php if ($fs->prefs['repeat_emailaddress']): ?>
		<li class="required">
			<label for="verifyemailaddress"><?= eL('verifyemailaddress') ?></label>
			<div class="valuewrap">
				<input id="verifyemailaddress" value="<?php echo Filters::noXSS(Req::val('verify_email_address')); ?>" name="verify_email_address" required="required" type="text" size="20" maxlength="100" class="fi-medium" />
			</div>
		</li>
		<?php endif ?>

		<?php if (!empty($fs->prefs['jabber_server'])): ?>
		<li>
			<label for="jabberid"><?= eL('jabberid') ?></label>
			<div class="valuewrap">
				<input id="jabberid" value="<?php echo Filters::noXSS(Req::val('jabber_id')); ?>" name="jabber_id" type="text" size="20" maxlength="100" class="fi-medium" />
			</div>
		</li>
		<?php endif ?>

		<li>
			<label for="notify_type"><?= eL('notifications') ?></label>
			<div class="valuewrap">
				<select id="notify_type" name="notify_type">
				<?php echo tpl_options($fs->getNotificationOptions(), Req::val('notify_type')); ?>
				</select>
			</div>
		</li>

		<li>
			<label for="time_zone"><?= eL('timezone') ?></label>
			<div class="valuewrap">
				<select id="time_zone" name="time_zone">
				<?php
					$times = array();
					for ($i = -12; $i <= 13; $i++) {
						$times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
					}
				?>
				<?php echo tpl_options($times, Req::val('time_zone', 0)); ?>
				</select>
			</div>
		</li>
		<?php if($fs->prefs['captcha_securimage']) : ?>
		<li class="captchali">
			<label for="captcha_code"><?= eL('registercaptcha') ?></label>
			<div class="valuewrap">
				<div class="securimage"><?php echo $captcha_securimage_html; ?></div>
			</div>
		</li>
		<?php endif; ?>
	</ul>
	<div class="buttons">
		<input type="hidden" name="action" value="register.sendcode" />
		<?php
			if(isset($fs->prefs['captcha_recaptcha']) && $fs->prefs['captcha_recaptcha']
				&& isset($fs->prefs['captcha_recaptcha_sitekey']) && $fs->prefs['captcha_recaptcha_sitekey']
				&& isset($fs->prefs['captcha_recaptcha_secret']) && $fs->prefs['captcha_recaptcha_secret']
			): ?>
		<div class="g-recaptcha" data-sitekey="<?php echo Filters::noXSS($fs->prefs['captcha_recaptcha_sitekey']); ?>"></div>
		<noscript>Javascript is required for this Google reCAPTCHA.</noscript>
		<?php endif; ?>
		<button type="submit" name="buSubmit" id="buSubmit"><?= eL('sendcode') ?></button>
	</div>

	<p><?= L('note') ?></p>
</form>
</div>
