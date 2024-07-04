<?php
/* quick solution
* https://developers.google.com/recaptcha/docs/verify
*/
class recaptcha
{
	static function verify(){
		global $fs;

		if (!isset($_POST['g-recaptcha-response']) or !is_string($_POST['g-recaptcha-response'])) {
			return false;
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => $fs->prefs['captcha_recaptcha_secret'],
			'response' => $_POST['g-recaptcha-response']
		);

		$options = array(
			'http' => array (
				'method' => 'POST',
				/* for php5.3, default enctype for http_build_query() was added with php5.4, http://php.net/manual/en/function.http-build-query.php */
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($data, '', '&')
			)
		);

		$context = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success=json_decode($verify);

		return $captcha_success->success;
	}

} # end class
