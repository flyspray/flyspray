<?php

  /********************************************************\
  | CAS authentication (no output)                        |
  | ~~~~~~~~~~~~~~~~~~~                                    |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

// Enable debugging
phpCAS::setDebug();

if (! isset($_SESSION['return_to'])) {
    $_SESSION['return_to'] = base64_decode(Get::val('return_to', ''));
    $_SESSION['return_to'] = $_SESSION['return_to'] ?: $baseurl;
}

$return_to = $_SESSION['return_to'];
unset($_SESSION['return_to']);

function setupCASClient()
{
	global $conf;
	// Get the CAS server version (default to SAML_VERSION_1_1).
	// See: https://developer.jasig.org/cas-clients/php/1.3.4/docs/api/group__public.html
	$cas_port = 443;
	$cas_version = SAML_VERSION_1_1;
	if ( $conf['cas']['cas_version'] === 'CAS_VERSION_3_0' ) {
		$cas_version = CAS_VERSION_3_0;
	} else if ( $conf['cas']['cas_version'] === 'CAS_VERSION_2_0' ) {
		$cas_version = CAS_VERSION_2_0;
	} else if ( $conf['cas']['cas_version'] === 'CAS_VERSION_1_0' ) {
		$cas_version = CAS_VERSION_1_0;
	}
	if (isset( $conf['cas']['cas_port']) && strlen( $conf['cas']['cas_port'] ) > 0 ) {
		$cas_port = intval( $conf['cas']['cas_port'] );
	}

	// Set the CAS client configuration
	phpCAS::client( $cas_version, $conf['cas']['cas_host'], intval( $conf['cas']['cas_port'] ), $conf['cas']['cas_context']);
	$cacert_path = BASEDIR . "/". $conf['cas']['cas_server_ca_cert_path'];
	$time_90_days = 90 * 24 * 60 * 60; // days * hours * minutes * seconds
	$time_90_days_ago = time() - $time_90_days;
	if ( ! file_exists( $cacert_path ) || filemtime( $cacert_path ) < $time_90_days_ago ) {
		$cacert_contents = file_get_contents($conf['cas']['cas_server_ca_cert_url']);
		if ( $cacert_contents !== false ) {
			file_put_contents( $cacert_path, $cacert_contents );
		} else {
			Flyspray::show_error('Unable to update outdated server certificates.');
			return false;
		}
	}
	phpCAS::setCasServerCACert( $cacert_path );
	return true;
}

function authCASClient()
{
	global $return_to;
	// Authenticate against CAS
	try {
		if ( ! phpCAS::isAuthenticated() ) {
			phpCAS::forceAuthentication();
			die();
		}
	} catch ( CAS_AuthenticationException $e ) {
		// CAS server threw an error in isAuthenticated(), potentially because
		// the cached ticket is outdated. Try renewing the authentication.
		try {
			phpCAS::renewAuthentication();
		} catch ( CAS_AuthenticationException $e ) {
			Flyspray::show_error('CAS server returned an Authentication Exception.');
			phpCAS::logoutWithRedirectService( $return_to);
			die();
		}
	}
}

function getUserDetails()
{
	global $conf;
	// Get the TLD from the CAS host for use in matching email addresses
	// For example: example.edu is the TLD for authn.example.edu, so user
	// 'bob' will have the following email address: bob@example.edu.
	$tld = preg_match( '/[^.]*\.[^.]*$/', $conf['cas']['cas_host'], $matches ) === 1 ? $matches[0] : '';

	// Get username that successfully authenticated against the external service (CAS).
	$externally_authenticated_email = strtolower( phpCAS::getUser() ) . '@' . $tld;

	// Retrieve the user attributes (e.g., email address, first name, last name) from the CAS server.
	$cas_attributes = phpCAS::getAttributes();

	// If a CAS attribute has been specified as containing the email address, use that instead.
	// Email attribute can be a string or an array of strings.
	if (
		isset($conf['cas']['cas_attr_email']) &&
		strlen( $conf['cas']['cas_attr_email'] ) > 0 &&
		array_key_exists( $conf['cas']['cas_attr_email'], $cas_attributes ) && (
			(
				is_array( $cas_attributes[$conf['cas']['cas_attr_email']] ) &&
				count( $cas_attributes[$conf['cas']['cas_attr_email']] ) > 0
			) || (
				is_string( $cas_attributes[$conf['cas']['cas_attr_email']] ) &&
				strlen( $cas_attributes[$conf['cas']['cas_attr_email']] ) > 0
			)
		)
	) {
		$externally_authenticated_email = $cas_attributes[$conf['cas']['cas_attr_email']];
	}

	// Get username (as specified by the CAS server).
	$username = phpCAS::getUser();

	// Get user first name and last name.
	$first_name = isset( $conf['cas']['cas_attr_first_name']) && strlen( $conf['cas']['cas_attr_first_name'] ) > 0 && array_key_exists( $conf['cas']['cas_attr_first_name'], $cas_attributes ) && strlen( $cas_attributes[$conf['cas']['cas_attr_first_name']] ) > 0 ? $cas_attributes[$conf['cas']['cas_attr_first_name']] : '';
	$last_name = isset( $conf['cas']['cas_attr_last_name']) && strlen( $conf['cas']['cas_attr_last_name'] ) > 0 && array_key_exists( $conf['cas']['cas_attr_last_name'], $cas_attributes ) && strlen( $conf['cas']['cas_attr_last_name'] ) > 0 ? $cas_attributes[$conf['cas']['cas_attr_last_name']] : '';
	$user_id = isset( $conf['cas']['cas_attr_user_id']) && strlen( $conf['cas']['cas_attr_user_id'] ) > 0 && array_key_exists( $conf['cas']['cas_attr_user_id'], $cas_attributes ) && strlen( $conf['cas']['cas_attr_user_id'] ) > 0 ? $cas_attributes[$conf['cas']['cas_attr_user_id']] : '';

	return array(
		'email' => $externally_authenticated_email,
		'username' => $username,
		'first_name' => $first_name,
		'last_name' => $last_name,
		'user_id' => $user_id,
		'authenticated_by' => 'cas',
	);
}

function checkCASAttrs($user_details)
{
	if (strlen($user_details['email']) <= 0) {
		Flyspray::show_error('Check your cas config, "email" attritube is required');
	}else if (strlen($user_details['username']) <= 0) {
		Flyspray::show_error('Check your cas config, "username" attritube is required');
	}else if (strlen($user_details['user_id']) <= 0) {
		Flyspray::show_error('Check your cas config, "user_id" attritube is required');
	}
}

if (! setupCASClient()) {
	Flyspray::Redirect($return_to);
}

if (Req::val('logout')) {
    $user->logout();
    phpCAS::logoutWithRedirectService($return_to);
    Flyspray::Redirect($return_to);
}

// check CAS authentication
$auth = phpCAS::checkAuthentication();
if (! $auth) {
	authCASClient();
	die();
}

$user_details = getUserDetails();
checkCASAttrs($user_details);

$cas_uid = $user_details['user_id'];
$provider = $user_details['authenticated_by'];
$username = $user_details['username'];

// First time logging in
if (! Flyspray::checkForOauthUser($cas_uid, $provider)) {
	$email = $user_details['email'];
	$full_name = $user_details['last_name'].$user_details['first_name'];
	$real_name = $full_name ? $full_name : $username;

	if ($cas_uid === "1") {
		$group_in = "1";
	}else{
		$group_in = $fs->prefs['anon_group'];
	}
	$fs_uid = Flyspray::UserNameToId($username);
	if ( $fs_uid > 0 ) {
		// If username already exists, update 'users' table
		$db->Query('UPDATE {users} SET user_name = ?, email_address=?, real_name = ?, oauth_uid = ?, oauth_provider = ? WHERE user_id = ?',
			array($username, $email, $real_name, $cas_uid, $provider, $fs_uid));
		// and insert data in 'user_emails' table.
                $db->Query("INSERT INTO {user_emails}(id,email_address,oauth_uid,oauth_provider) VALUES (?,?,?,?)",
                        array($fs_uid, strtolower($email), $cas_uid, $provider));
	} else {
		Backend::create_user($username, null, $real_name, '', $email, 0, 0, $group_in, 1, $cas_uid, $provider);
	}
}

if (($user_id = Flyspray::checkLogin($username, null, 'oauth')) < 1) {
    Flyspray::show_error(23); // account disabled
}

$user = new User($user_id);

// Set a couple of cookies
$passweirded = crypt($user->infos['user_pass'], $conf['general']['cookiesalt']);
Flyspray::setCookie('flyspray_userid', $user->id, 0,null,null,null,true);
Flyspray::setCookie('flyspray_passhash', $passweirded, 0,null,null,null,true);
$_SESSION['SUCCESS'] = L('loginsuccessful');

Flyspray::Redirect($return_to);
?>
