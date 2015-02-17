<?php

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$providers = array(
    'github' => function() use ($conf) {
        if (empty($conf['oauth']['github_secret']) ||
            empty($conf['oauth']['github_id'])     ||
            empty($conf['oauth']['github_redirect'])) {

            throw new Exception('Config error make sure the github_* variables are set.');
        }
        return new GithubProvider(array(
            'clientId'     =>  $conf['oauth']['github_id'],
            'clientSecret' =>  $conf['oauth']['github_secret'],
            'redirectUri'  =>  $conf['oauth']['github_redirect'],
            'scopes'       => array('user:email')
        ));
    },
    'google' => function() use ($conf) {
        if (empty($conf['oauth']['google_secret']) ||
            empty($conf['oauth']['google_id'])     ||
            empty($conf['oauth']['google_redirect'])) {

            throw new Exception('Config error make sure the google_* variables are set.');
        }
        return new League\OAuth2\Client\Provider\Google(array(
            'clientId'     =>  $conf['oauth']['google_id'],
            'clientSecret' =>  $conf['oauth']['google_secret'],
            'redirectUri'  =>  $conf['oauth']['google_redirect'],
            'scopes'       => array('email', 'profile')
        ));
    },
    'facebook' => function() use ($conf) {
        if (empty($conf['oauth']['facebook_secret']) ||
            empty($conf['oauth']['facebook_id'])     ||
            empty($conf['oauth']['facebook_redirect'])) {

            throw new Exception('Config error make sure the facebook_* variables are set.');
        }
        return new League\OAuth2\Client\Provider\Facebook(array(
            'clientId'     =>  $conf['oauth']['facebook_id'],
            'clientSecret' =>  $conf['oauth']['facebook_secret'],
            'redirectUri'  =>  $conf['oauth']['facebook_redirect'],
        ));
    },
    'microsoft' => function() use ($conf) {
        if (empty($conf['oauth']['microsoft_secret']) ||
            empty($conf['oauth']['microsoft_id'])     ||
            empty($conf['oauth']['microsoft_redirect'])) {

            throw new Exception('Config error make sure the microsoft_* variables are set.');
        }
        return new League\OAuth2\Client\Provider\Microsoft(array(
            'clientId'     =>  $conf['oauth']['microsoft_id'],
            'clientSecret' =>  $conf['oauth']['microsoft_secret'],
            'redirectUri'  =>  $conf['oauth']['microsoft_redirect'],
        ));
    },
);

if (! isset($_SESSION['return_to'])) {
    $_SESSION['return_to'] = base64_decode(Get::val('return_to', ''));
    $_SESSION['return_to'] = $_SESSION['return_to'] ?: $baseurl;
}

$provider = isset($_SESSION['oauth_provider']) ? $_SESSION['oauth_provider'] : 'none';
$provider = strtolower(Get::val('provider', $provider));
unset($_SESSION['oauth_provider']);

if (!in_array($provider, $conf['oauth']['enabled'])) {
    Flyspray::show_error(26);
}

$obj = $providers[$provider]();

if ( ! Get::has('code') && ! Post::has('username')) {
    // get authorization code
    header('Location: '.$obj->getAuthorizationUrl());
    exit;
}

if (isset($_SESSION['oauth_token'])) {
    $token = unserialize($_SESSION['oauth_token']);
    unset($_SESSION['oauth_token']);
} else {
    // Try to get an access token
    try {
        $token = $obj->getAccessToken('authorization_code', array('code' => $_GET['code']));
    } catch (\League\OAuth2\Client\Exception\IDPException $e) {
        throw new Exception($e->getMessage());
    }
}

$user_details = $obj->getUserDetails($token);
$uid          = $user_details->uid;

if (Post::has('username')) {
    $username = Post::val('username');
} else {
    $username = $user_details->nickname;
}

// First time logging in
if (! Flyspray::checkForOauthUser($uid, $provider)) {
    if (! $user_details->email) {
        Flyspray::show_error(27);
    }

    $success = false;

    if ($username) {
        $group_in = $fs->prefs['anon_group'];
        $name     = $user_details->name ?: $username;
        $success  = Backend::create_user($username, null, $name, '', $user_details->email, 0, 0, $group_in, 1, $uid, $provider);
    }

    // username taken or not provided, ask for it
    if (!$success) {
        $_SESSION['oauth_token']    = serialize($token);
        $_SESSION['oauth_provider'] = $provider;
        $page->assign('provider', ucfirst($provider));
        $page->assign('username', $username);
        $page->pushTpl('register.oauth.tpl');
        return;
    }
}

if (($user_id = Flyspray::checkLogin($user_details->email, null, 'oauth')) < 1) {
    Flyspray::show_error(23); // account disabled
}

$user = new User($user_id);

// Set a couple of cookies
$passweirded = crypt($user->infos['user_pass'], $conf['general']['cookiesalt']);
Flyspray::setcookie('flyspray_userid', $user->id, 0);
Flyspray::setcookie('flyspray_passhash', $passweirded, 0);
$_SESSION['SUCCESS'] = L('loginsuccessful');

$return_to = $_SESSION['return_to'];
unset($_SESSION['return_to']);

Flyspray::Redirect($return_to);
