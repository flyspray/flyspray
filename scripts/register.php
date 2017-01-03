<?php

  /*********************************************************\
  | Register a new user (when confirmation codes is used)   |
  | ~~~~~~~~~~~~~~~~~~~                                     |
  \*********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$page->setTitle($fs->prefs['page_title'] . L('registernewuser'));

if (!$user->isAnon()) {
    Flyspray::Redirect($baseurl);
}

if ($user->can_register()) {
    // 32 is the length of the magic_url
    if (Req::has('magic_url') && strlen(Req::val('magic_url')) == 32) {
        // If the user came here from their notification link
        $sql = $db->Query('SELECT * FROM {registrations} WHERE magic_url = ?',
                          array(Get::val('magic_url')));

        if (!$db->CountRows($sql)) {
            Flyspray::show_error(18);
        }

        $page->pushTpl('register.magic.tpl');
    } else {
		if($fs->prefs['captcha_securimage']){
			$captchaoptions = array(
				'input_name' => 'captcha_code',
				'show_audio_button' => false,
				'disable_flash_fallback' => true
			);
			$captcha_securimage_html=Securimage::getCaptchaHtml($captchaoptions);
			$page->assign('captcha_securimage_html', $captcha_securimage_html);
		}

        $page->pushTpl('register.no-magic.tpl');
    }
} elseif ($user->can_self_register()) {
	if($fs->prefs['captcha_securimage']){
		$captchaoptions = array(
			'input_name' => 'captcha_code',
			'show_audio_button' => false,
			'disable_flash_fallback' => true,
			'image_attributes' =>array('style'=>'')
		);
		$captcha_securimage_html=Securimage::getCaptchaHtml($captchaoptions);
		$page->assign('captcha_securimage_html', $captcha_securimage_html);
	}

	$page->pushTpl('common.newuser.tpl');
} else {
	Flyspray::show_error(22);
}
?>
