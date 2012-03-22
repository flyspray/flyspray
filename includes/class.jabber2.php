<?php
/**
 * Jabber class
 *
 * @version $Id$
 * @copyright 2006 Flyspray.org
 * @notes: This lib has been created due to the lack of any good and modern jabber class out there
 * @author: Florian Schmitz (floele)
 */

define('SECURITY_NONE', 0);
define('SECURITY_SSL', 1);
define('SECURITY_TLS', 2);

class Jabber
{
    public $connection = null;
    public $session = array();
    public $resource = 'class.jabber2.php';
    public $log = array();
    public $log_enabled = true;
    public $timeout = 10;
    public $user = '';
    public $password = '';
    public $server = '';
    public $features = array();

    public function __construct($login, $password, $security = SECURITY_NONE, $port = 5222, $host = '')
    {
        // Can we use Jabber at all?
        // Note: Maybe replace with SimpleXML in the future
        if (!extension_loaded('xml')) {
            $this->log('Error: No XML functions available, Jabber functions can not operate.');
            return false;
        }

        //bug in php 5.2.1 renders this stuff more or less useless.
        if ((version_compare(phpversion(), '5.2.1', '>=') && version_compare(phpversion(), '5.2.3RC2', '<')) && $security != SECURITY_NONE) {
            $this->log('Error: PHP ' . phpversion() . ' + SSL is incompatible with jabber, see http://bugs.php.net/41236');
            return false;
        }

        if (!Jabber::check_jid($login)) {
            $this->log('Error: Jabber ID is not valid: ' . $login);
            return false;
        }

        // Extract data from user@server.org
        list($username, $server) = explode('@', $login);

        // Decide whether or not to use encryption
        if ($security == SECURITY_SSL && !Jabber::can_use_ssl()) {
            $this->log('Warning: SSL encryption is not supported (openssl required). Falling back to no encryption.');
            $security = SECURITY_NONE;
        }
        if ($security == SECURITY_TLS && !Jabber::can_use_tls()) {
            $this->log('Warning: TLS encryption is not supported (openssl and stream_socket_enable_crypto() required). Falling back to no encryption.');
            $security = SECURITY_NONE;
        }

        $this->session['security'] = $security;
        $this->server              = $server;
        $this->user                = $username;
        $this->password            = $password;

        if ($this->open_socket( ($host != '') ? $host : $server, $port, $security == SECURITY_SSL)) {
            $this->send("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
            $this->send("<stream:stream to='{$server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");
        } else {
            return false;
        }
        // Now we listen what the server has to say...and give appropriate responses
        $this->response($this->listen());
    }
    
    /**
     * Sets the resource which is used. No validation is done here, only escaping.
    * @param string $$name
     * @access public
     */
    public function SetResource($name)
    {
        $this->resource = $name;
    }

    /**
     * Send data to the Jabber server
     * @param string $xml
     * @access public
     * @return bool
     */
    public function send($xml)
    {
        if ($this->connected()) {
           $xml = trim($xml);
           $this->log('SEND: '. $xml);
           return fwrite($this->connection, $xml);
        } else {
            $this->log('Error: Could not send, connection lost (flood?).');
            return false;
        }
    }

    /**
     * OpenSocket
     * @param string $server host to connect to
     * @param int $port port number
     * @param bool $ssl use ssl or not
     * @access public
     * @return bool
     */
    public function open_socket($server, $port, $ssl = false)
    {
        if (function_exists("dns_get_record")) {
            $record = dns_get_record("_xmpp-client._tcp.$server", DNS_SRV);
            if (!empty($record)) {
                $server = $record[0]['target'];
            }
        } else {
            $this->log('Warning: dns_get_record function not found. gtalk will not work.');
        }

        $server = $ssl ? 'ssl://' . $server : $server;

        if ($ssl) {
            $this->session['ssl'] = true;
        }

        if ($this->connection = @fsockopen($server, $port, $errorno, $errorstr, $this->timeout)) {
            socket_set_blocking($this->connection, 0);
            socket_set_timeout($this->connection, 60);

            return true;
        }
        // Apparently an error occured...
        $this->log('Error: ' . $errorstr);
        return false;
    }

    public function log($msg)
    {
        if ($this->log_enabled) {
            $this->log[] = $msg;
            return true;
        }

        return false;
    }

    /**
     * Listens to the connection until it gets data or the timeout is reached.
     * Thus, it should only be called if data is expected to be received.
     * @access public
     * @return mixed either false for timeout or an array with the received data
     */
    public function listen($timeout = 10, $wait = false)
    {
        if (!$this->connected()) {
            return false;
        }

        // Wait for a response until timeout is reached
        $start = time();
        $data = '';

        do {
            $read = trim(fread($this->connection, 4096));
            $data .= $read;
        } while (time() <= $start + $timeout && !feof($this->connection) && ($wait || $data == '' || $read != ''
                                                 || (substr(rtrim($data), -1) != '>')));

        if ($data != '') {
            $this->log('RECV: '. $data);
            return Jabber::xmlize($data);
        } else {
            $this->log('Timeout, no response from server.');
            return false;
        }
    }

    /**
     * Initiates login (using data from contructor)
     * @access public
     * @return bool
     */
    public function login()
    {
        if (!count($this->features)) {
            $this->log('Error: No feature information from server available.');
            return false;
        }

        return $this->response($this->features);
    }

    /**
     * Initiates account registration (based on data used for contructor)
     * @access public
     * @return bool
     */
    public function register()
    {
        if (!isset($this->session['id']) || isset($this->session['jid'])) {
            $this->log('Error: Cannot initiate registration.');
            return false;
        }

        $this->send("<iq type='get' id='reg_1'>
                       <query xmlns='jabber:iq:register'/>
                     </iq>");
        return $this->response($this->listen());
    }

    /**
     * Initiates account un-registration (based on data used for contructor)
     * @access public
     * @return bool
     */
    public function unregister()
    {
        if (!isset($this->session['id']) || !isset($this->session['jid'])) {
            $this->log('Error: Cannot initiate un-registration.');
            return false;
        }

        $this->send("<iq type='set' from='" . Jabber::jspecialchars($this->session['jid']) . "' id='unreg_1'>
                       <query xmlns='jabber:iq:register'>
                         <remove/>
                       </query>
                     </iq>");
        return $this->response($this->listen(2)); // maybe we don't even get a response
    }

    /**
     * Sets account presence. No additional info required (default is "online" status)
     * @param $type dnd, away, chat, xa or nothing
     * @param $message
     * @param $unavailable set this to true if you want to become unavailable
     * @access public
     * @return bool
     */
    public function presence($type = '', $message = '', $unavailable = false)
    {
        if (!isset($this->session['jid'])) {
            $this->log('Error: Cannot set presence at this point.');
            return false;
        }

        if (in_array($type, array('dnd', 'away', 'chat', 'xa'))) {
            $type = '<show>'. $type .'</show>';
        } else {
            $type = '';
        }

        $unavailable = ($unavailable) ? " type='unavailable'" : '';
        $message = ($message) ? '<status>' . Jabber::jspecialchars($message) .'</status>' : '';

        $this->session['sent_presence'] = !$unavailable;

        return $this->send("<presence$unavailable>" .
                              $type .
                              $message .
                           '</presence>');
    }

    /**
     * This handles all the different XML elements
     * @param array $xml
     * @access public
     * @return bool
     */
    public function response($xml)
    {
        if (!is_array($xml) || !count($xml)) {
            return false;
        }

        // did we get multiple elements? do one after another
        // array('message' => ..., 'presence' => ...)
        if (count($xml) > 1) {
            foreach ($xml as $key => $value) {
                $this->response(array($key => $value));
            }
            return;
        } else
        // or even multiple elements of the same type?
        // array('message' => array(0 => ..., 1 => ...))
        if (count(reset($xml)) > 1) {
            foreach (reset($xml) as $value) {
                $this->response(array(key($xml) => array(0 => $value)));
            }
            return;
        }

        switch (key($xml)) {
            case 'stream:stream':
                // Connection initialised (or after authentication). Not much to do here...
                if (isset($xml['stream:stream'][0]['#']['stream:features'])) {
                    // we already got all info we need
                    $this->features = $xml['stream:stream'][0]['#'];
                } else {
                    $this->features = $this->listen();
                }
                $second_time = isset($this->session['id']);
                $this->session['id'] = $xml['stream:stream'][0]['@']['id'];
                if ($second_time) {
                    // If we are here for the second time after TLS, we need to continue logging in
                    $this->login();
                    return;
                }

                // go on with authentication?
                if (isset($this->features['stream:features'][0]['#']['bind'])) {
                    return $this->response($this->features);
                }
                break;

            case 'stream:features':
                // Resource binding after successful authentication
                if (isset($this->session['authenticated'])) {
                    // session required?
                    $this->session['sess_required'] = isset($xml['stream:features'][0]['#']['session']);

					$this->send("<iq type='set' id='bind_1'>
								   <bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'>
							    	 <resource>" . Jabber::jspecialchars($this->resource) . "</resource>
                                   </bind>
                                 </iq>");
                    return $this->response($this->listen());
                }
                // Let's use TLS if SSL is not enabled and we can actually use it
                if ($this->session['security'] == SECURITY_TLS && isset($xml['stream:features'][0]['#']['starttls'])) {
                    $this->log('Switching to TLS.');
                    $this->send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>\n");
                    return $this->response($this->listen());
                }
                // Does the server support SASL authentication?

                // I hope so, because we do (and no other method).
                if (isset($xml['stream:features'][0]['#']['mechanisms'][0]['@']['xmlns']) &&
                    $xml['stream:features'][0]['#']['mechanisms'][0]['@']['xmlns'] == 'urn:ietf:params:xml:ns:xmpp-sasl') {
                    // Now decide on method
                    $methods = array();
                    foreach ($xml['stream:features'][0]['#']['mechanisms'][0]['#']['mechanism'] as $value) {
                        $methods[] = $value['#'];
                    }

                    // we prefer this one
                    if (in_array('DIGEST-MD5', $methods)) {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='DIGEST-MD5'/>");
                    // we don't want to use this (neither does the server usually) if no encryption is in place
                    # http://www.xmpp.org/extensions/attic/jep-0078-1.7.html
                    # The plaintext mechanism SHOULD NOT be used unless the underlying stream is encrypted (using SSL or TLS)
                    # and the client has verified that the server certificate is signed by a trusted certificate authority.
                    } else if (in_array('PLAIN', $methods) && (isset($this->session['ssl']) || isset($this->session['tls']))) {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>"
                                      . base64_encode(chr(0) . $this->user . '@' . $this->server . chr(0) . $this->password) .
                                    "</auth>");
                    } else if (in_array('ANONYMOUS', $methods)) {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='ANONYMOUS'/>");
                    // not good...
                    } else {
                        $this->log('Error: No authentication method supported.');
                        $this->disconnect();
                        return false;
                    }
                    return $this->response($this->listen());

                } else {
                    // ok, this is it. bye.
                    $this->log('Error: Server does not offer SASL authentication.');
                    $this->disconnect();
                    return false;
                }
                break;

            case 'challenge':
                // continue with authentication...a challenge literally -_-
                $decoded = base64_decode($xml['challenge'][0]['#']);
                $decoded = Jabber::parse_data($decoded);
                if (!isset($decoded['digest-uri'])) {
                    $decoded['digest-uri'] = 'xmpp/'. $this->server;
                }

                // better generate a cnonce, maybe it's needed
                
                $decoded['cnonce'] = base64_encode(md5(uniqid(mt_rand(), true)));

                // second challenge?
                if (isset($decoded['rspauth'])) {
                    $this->send("<response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'/>");
                } else {
                    $response = array('username' => $this->user,
                                      'response' => $this->encrypt_password(array_merge($decoded, array('nc' => '00000001'))),
                                      'charset'  => 'utf-8',
                                      'nc'       => '00000001',
                                      'qop'      => 'auth'); // the only option we support anyway

                    foreach (array('nonce', 'digest-uri', 'realm', 'cnonce') as $key) {
                        if (isset($decoded[$key])) {
                            $response[$key] = $decoded[$key];
                        }
                    }

                    $this->send("<response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'>" .
                                 base64_encode(Jabber::implode_data($response))
                                 . "</response>");
                }

                return $this->response($this->listen());

            case 'failure':
                $this->log('Error: Server sent "failure".');
                $this->disconnect();
                return false;

            case 'proceed':
                // continue switching to TLS
                $meta = stream_get_meta_data($this->connection);
                socket_set_blocking($this->connection, 1);
                if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			        $this->log('Error: TLS mode change failed.');
                    return false;
                }
                socket_set_blocking($this->connection, $meta['blocked']);
                $this->session['tls'] = true;
                // new stream
                $this->send("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
                $this->send("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");

                return $this->response($this->listen());

            case 'success':
                // Yay, authentication successful.
                $this->send("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");
                $this->session['authenticated'] = true;
                return $this->response($this->listen()); // we have to wait for another response

            case 'iq':
                // we are not interested in IQs we did not expect
                if (!isset($xml['iq'][0]['@']['id'])) {
                    return false;
                }
                // multiple possibilities here
                switch ($xml['iq'][0]['@']['id'])
                {
                    case 'bind_1':
                        $this->session['jid'] = $xml['iq'][0]['#']['bind'][0]['#']['jid'][0]['#'];
                        // and (maybe) yet another request to be able to send messages *finally*
                        if ($this->session['sess_required']) {
                            $this->send("<iq to='{$this->server}'
                                             type='set'
                                             id='sess_1'>
                                          <session xmlns='urn:ietf:params:xml:ns:xmpp-session'/>
                                        </iq>");
                            return $this->response($this->listen());
                        }
                        return true;

                    case 'sess_1':
                        return true;

                    case 'reg_1':
                        $this->send("<iq type='set' id='reg_2'>
                                       <query xmlns='jabber:iq:register'>
                                         <username>" . Jabber::jspecialchars($this->user) . "</username>
                                         <password>" . Jabber::jspecialchars($this->password) . "</password>
                                       </query>
                                     </iq>");
                        return $this->response($this->listen());

                    case 'reg_2':
                        // registration end
                        if (isset($xml['iq'][0]['#']['error'])) {
                            $this->log('Warning: Registration failed.');
                            return false;
                        }
                        return true;

                    case 'unreg_1':
                        return true;

                    default:
                        $this->log('Notice: Received unexpected IQ.');
                        return false;
                }
                break;

            case 'message':
                // we are only interested in content...
                if (!isset($xml['message'][0]['#']['body'])) {
                    return false;
                }

                $message['body'] = $xml['message'][0]['#']['body'][0]['#'];
                $message['from'] = $xml['message'][0]['@']['from'];
                if (isset($xml['message'][0]['#']['subject'])) {
                    $message['subject'] = $xml['message'][0]['#']['subject'][0]['#'];
                }
                $this->session['messages'][] = $message;
                break;

            default:
                // hm...don't know this response
                $this->log('Notice: Unknown server response (' . key($xml) . ')');
                return false;
        }
    }

    public function send_message($to, $text, $subject = '', $type = 'normal')
    {
        if (!isset($this->session['jid'])) {
            return false;
        }

        if (!in_array($type, array('chat', 'normal', 'error', 'groupchat', 'headline'))) {
            $type = 'normal';
        }

        return $this->send("<message from='" . Jabber::jspecialchars($this->session['jid']) . "'
                                     to='" . Jabber::jspecialchars($to) . "'
                                     type='$type'
                                     id='" . uniqid('msg') . "'>
                              <subject>" . Jabber::jspecialchars($subject) . "</subject>
                              <body>" . Jabber::jspecialchars($text) . "</body>
                            </message>");
    }

    public function get_messages($waitfor = 3)
    {
        if (!isset($this->session['sent_presence']) || !$this->session['sent_presence']) {
            $this->presence();
        }

        if ($waitfor > 0) {
            $this->response($this->listen($waitfor, $wait = true)); // let's see if any messages fly in
        }

        return isset($this->session['messages']) ? $this->session['messages'] : array();
    }

    public function connected()
    {
        return is_resource($this->connection) && !feof($this->connection);
    }

    public function disconnect()
    {
        if ($this->connected()) {
            // disconnect gracefully
            if (isset($this->session['sent_presence'])) {
                $this->presence('', 'offline', $unavailable = true);
            }
            $this->send('</stream:stream>');
            $this->session = array();
            return fclose($this->connection);
        }
        return false;
    }

    public static function can_use_ssl()
    {
        return extension_loaded('openssl');
    }

    public static function can_use_tls()
    {
        return Jabber::can_use_ssl() && function_exists('stream_socket_enable_crypto');
    }

    /**
     * Encrypts a password as in RFC 2831
     * @param array $data Needs data from the client-server connection
     * @access public
     * @return string
     */
    public function encrypt_password($data)
    {
        // let's me think about <challenge> again...
        foreach (array('realm', 'cnonce', 'digest-uri') as $key) {
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }

        $pack = md5($this->user . ':' . $data['realm'] . ':' . $this->password);
        if (isset($data['authzid'])) {
            $a1 = pack('H32', $pack)  . sprintf(':%s:%s:%s', $data['nonce'], $data['cnonce'], $data['authzid']);
        } else {
            $a1 = pack('H32', $pack)  . sprintf(':%s:%s', $data['nonce'], $data['cnonce']);
        }

        // should be: qop = auth
        $a2 = 'AUTHENTICATE:'. $data['digest-uri'];

        return md5(sprintf('%s:%s:%s:%s:%s:%s', md5($a1), $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], md5($a2)));
    }

    /**
     * parse_data like a="b",c="d",...
     * @param string $data
     * @access public
     * @return array a => b ...
     */
    public function parse_data($data)
    {
        // super basic, but should suffice
        $data = explode(',', $data);
        $pairs = array();
        foreach ($data as $pair) {
            $dd = strpos($pair, '=');
            if ($dd) {
                $pairs[substr($pair, 0, $dd)] = trim(substr($pair, $dd + 1), '"');
            }
        }
        return $pairs;
    }

    /**
     * opposite of Jabber::parse_data()
     * @param array $data
     * @access public
     * @return string
     */
    public function implode_data($data)
    {
        $return = array();
        foreach ($data as $key => $value) {
            $return[] = $key . '="' . $value . '"';
        }
        return implode(',', $return);
    }

    /**
     * Checks whether or not a Jabber ID is valid (FS#1131)
     * @param string $jid
     * @access public
     * @return string
     */
    public function check_jid($jid)
    {
        $i = strpos($jid, '@');
        if ($i === false) {
            return false;
        }

        $username = substr($jid, 0, $i);
        $realm = substr($jid, $i + 1);

        if (strlen($username) == 0 || strlen($realm) < 3) {
            return false;
        }

        $arr = explode('.', $realm);

        if (count($arr) == 0) {
            return false;
        }

        foreach ($arr as $part)
        {
            if (substr($part, 0, 1) == '-' || substr($part, -1, 1) == '-') {
                return false;
            }

            if (preg_match("@^[a-zA-Z0-9-.]+$@", $part) == false) {
                return false;
            }
        }

        $b = array(array(0, 127), array(192, 223), array(224, 239),
                   array(240, 247), array(248, 251), array(252, 253));

        // Prohibited Characters RFC3454 + RFC3920
        $p = array(
            // Table C.1.1
            array(0x0020, 0x0020),		// SPACE
            // Table C.1.2
            array(0x00A0, 0x00A0),		// NO-BREAK SPACE
            array(0x1680, 0x1680),		// OGHAM SPACE MARK
            array(0x2000, 0x2001),		// EN QUAD
            array(0x2001, 0x2001),		// EM QUAD
            array(0x2002, 0x2002),		// EN SPACE
            array(0x2003, 0x2003),		// EM SPACE
            array(0x2004, 0x2004),		// THREE-PER-EM SPACE
            array(0x2005, 0x2005),		// FOUR-PER-EM SPACE
            array(0x2006, 0x2006),		// SIX-PER-EM SPACE
            array(0x2007, 0x2007),		// FIGURE SPACE
            array(0x2008, 0x2008),		// PUNCTUATION SPACE
            array(0x2009, 0x2009),		// THIN SPACE
            array(0x200A, 0x200A),		// HAIR SPACE
            array(0x200B, 0x200B),		// ZERO WIDTH SPACE
            array(0x202F, 0x202F),		// NARROW NO-BREAK SPACE
            array(0x205F, 0x205F),		// MEDIUM MATHEMATICAL SPACE
            array(0x3000, 0x3000),		// IDEOGRAPHIC SPACE
            // Table C.2.1
            array(0x0000, 0x001F),		// [CONTROL CHARACTERS]
            array(0x007F, 0x007F),		// DELETE
            // Table C.2.2
            array(0x0080, 0x009F),		// [CONTROL CHARACTERS]
            array(0x06DD, 0x06DD),		// ARABIC END OF AYAH
            array(0x070F, 0x070F),		// SYRIAC ABBREVIATION MARK
            array(0x180E, 0x180E),		// MONGOLIAN VOWEL SEPARATOR
            array(0x200C, 0x200C), 		// ZERO WIDTH NON-JOINER
            array(0x200D, 0x200D),		// ZERO WIDTH JOINER
            array(0x2028, 0x2028),		// LINE SEPARATOR
            array(0x2029, 0x2029),		// PARAGRAPH SEPARATOR
            array(0x2060, 0x2060),		// WORD JOINER
            array(0x2061, 0x2061),		// FUNCTION APPLICATION
            array(0x2062, 0x2062),		// INVISIBLE TIMES
            array(0x2063, 0x2063),		// INVISIBLE SEPARATOR
            array(0x206A, 0x206F),		// [CONTROL CHARACTERS]
            array(0xFEFF, 0xFEFF),		// ZERO WIDTH NO-BREAK SPACE
            array(0xFFF9, 0xFFFC),		// [CONTROL CHARACTERS]
            array(0x1D173, 0x1D17A),	// [MUSICAL CONTROL CHARACTERS]
            // Table C.3
            array(0xE000, 0xF8FF),		// [PRIVATE USE, PLANE 0]
            array(0xF0000, 0xFFFFD),	// [PRIVATE USE, PLANE 15]
            array(0x100000, 0x10FFFD),	// [PRIVATE USE, PLANE 16]
            // Table C.4
            array(0xFDD0, 0xFDEF),		// [NONCHARACTER CODE POINTS]
            array(0xFFFE, 0xFFFF),		// [NONCHARACTER CODE POINTS]
            array(0x1FFFE, 0x1FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x2FFFE, 0x2FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x3FFFE, 0x3FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x4FFFE, 0x4FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x5FFFE, 0x5FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x6FFFE, 0x6FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x7FFFE, 0x7FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x8FFFE, 0x8FFFF),	// [NONCHARACTER CODE POINTS]
            array(0x9FFFE, 0x9FFFF),	// [NONCHARACTER CODE POINTS]
            array(0xAFFFE, 0xAFFFF),	// [NONCHARACTER CODE POINTS]
            array(0xBFFFE, 0xBFFFF),	// [NONCHARACTER CODE POINTS]
            array(0xCFFFE, 0xCFFFF),	// [NONCHARACTER CODE POINTS]
            array(0xDFFFE, 0xDFFFF),	// [NONCHARACTER CODE POINTS]
            array(0xEFFFE, 0xEFFFF),	// [NONCHARACTER CODE POINTS]
            array(0xFFFFE, 0xFFFFF),	// [NONCHARACTER CODE POINTS]
            array(0x10FFFE, 0x10FFFF),	// [NONCHARACTER CODE POINTS]
            // Table C.5
            array(0xD800, 0xDFFF),		// [SURROGATE CODES]
            // Table C.6
            array(0xFFF9, 0xFFF9),		// INTERLINEAR ANNOTATION ANCHOR
            array(0xFFFA, 0xFFFA),		// INTERLINEAR ANNOTATION SEPARATOR
            array(0xFFFB, 0xFFFB),		// INTERLINEAR ANNOTATION TERMINATOR
            array(0xFFFC, 0xFFFC),		// OBJECT REPLACEMENT CHARACTER
            array(0xFFFD, 0xFFFD),		// REPLACEMENT CHARACTER
            // Table C.7
            array(0x2FF0, 0x2FFB),		// [IDEOGRAPHIC DESCRIPTION CHARACTERS]
            // Table C.8
            array(0x0340, 0x0340),		// COMBINING GRAVE TONE MARK
            array(0x0341, 0x0341),		// COMBINING ACUTE TONE MARK
            array(0x200E, 0x200E),		// LEFT-TO-RIGHT MARK
            array(0x200F, 0x200F),		// RIGHT-TO-LEFT MARK
            array(0x202A, 0x202A),		// LEFT-TO-RIGHT EMBEDDING
            array(0x202B, 0x202B),		// RIGHT-TO-LEFT EMBEDDING
            array(0x202C, 0x202C),		// POP DIRECTIONAL FORMATTING
            array(0x202D, 0x202D),		// LEFT-TO-RIGHT OVERRIDE
            array(0x202E, 0x202E),		// RIGHT-TO-LEFT OVERRIDE
            array(0x206A, 0x206A),		// INHIBIT SYMMETRIC SWAPPING
            array(0x206B, 0x206B),		// ACTIVATE SYMMETRIC SWAPPING
            array(0x206C, 0x206C),		// INHIBIT ARABIC FORM SHAPING
            array(0x206D, 0x206D),		// ACTIVATE ARABIC FORM SHAPING
            array(0x206E, 0x206E),		// NATIONAL DIGIT SHAPES
            array(0x206F, 0x206F),		// NOMINAL DIGIT SHAPES
            // Table C.9
            array(0xE0001, 0xE0001),	// LANGUAGE TAG
            array(0xE0020, 0xE007F),	// [TAGGING CHARACTERS]
            // RFC3920
            array(0x22, 0x22),			// "
            array(0x26, 0x26),			// &
            array(0x27, 0x27),			// '
            array(0x2F, 0x2F),			// /
            array(0x3A, 0x3A),			// :
            array(0x3C, 0x3C),			// <
            array(0x3E, 0x3E),			// >
            array(0x40, 0x40)			// @
        );

        $pos = 0;
        $result = true;

        while ($pos < strlen($username))
        {
            $len = 0;
            $uni = 0;
            for ($i = 0; $i <= 5; $i++)
            {
                if (ord($username[$pos]) >= $b[$i][0] && ord($username[$pos]) <= $b[$i][1])
                {
                    $len = $i + 1;

                    $uni = (ord($username[$pos]) - $b[$i][0]) * pow(2, $i * 6);

                    for ($k = 1; $k < $len; $k++) {
                        $uni += (ord($username[$pos + $k]) - 128) * pow(2, ($i - $k) * 6);
                    }

                    break;
                }
            }

            if ($len == 0) {
                return false;
            }

            foreach ($p as $pval)
            {
                if ($uni >= $pval[0] && $uni <= $pval[1]) {
                    $result = false;
                    break 2;
                }
            }

            $pos = $pos + $len;
        }

        return $result;
    }

    public static function jspecialchars($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'utf-8');
    }

    // ======================================================================
	// Third party code, taken from old jabber lib (the only usable code left)
	// ======================================================================

	// xmlize()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/

	public static function xmlize($data, $WHITE=1, $encoding='UTF-8') {

		$data = trim($data);
        if (substr($data, 0, 5) != '<?xml') {
            $data = '<root>'. $data . '</root>'; // mod
        }
		$vals = $array = array();
		$parser = xml_parser_create($encoding);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $WHITE);
		xml_parse_into_struct($parser, $data, $vals);
		xml_parser_free($parser);

		$i = 0;

		$tagname = $vals[$i]['tag'];
		if ( isset ($vals[$i]['attributes'] ) )
		{
			$array[$tagname][0]['@'] = $vals[$i]['attributes']; // mod
		} else {
			$array[$tagname][0]['@'] = array(); // mod
		}

		$array[$tagname][0]["#"] = Jabber::_xml_depth($vals, $i); // mod
        if (substr($data, 0, 5) != '<?xml') {
            $array = $array['root'][0]['#']; // mod
        }

		return $array;
	}



	// _xml_depth()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/

	public static function _xml_depth($vals, &$i) {
		$children = array();

		if ( isset($vals[$i]['value']) )
		{
			array_push($children, $vals[$i]['value']);
		}

		while (++$i < count($vals)) {

			switch ($vals[$i]['type']) {

				case 'open':

					if ( isset ( $vals[$i]['tag'] ) )
					{
						$tagname = $vals[$i]['tag'];
					} else {
						$tagname = '';
					}

					if ( isset ( $children[$tagname] ) )
					{
						$size = sizeof($children[$tagname]);
					} else {
						$size = 0;
					}

					if ( isset ( $vals[$i]['attributes'] ) ) {
						$children[$tagname][$size]['@'] = $vals[$i]["attributes"];

					}

					$children[$tagname][$size]['#'] = Jabber::_xml_depth($vals, $i);

					break;


				case 'cdata':
					array_push($children, $vals[$i]['value']);
					break;

				case 'complete':
					$tagname = $vals[$i]['tag'];

					if( isset ($children[$tagname]) )
					{
						$size = sizeof($children[$tagname]);
					} else {
						$size = 0;
					}

					if( isset ( $vals[$i]['value'] ) )
					{
						$children[$tagname][$size]["#"] = $vals[$i]['value'];
					} else {
						$children[$tagname][$size]["#"] = array();
					}

					if ( isset ($vals[$i]['attributes']) ) {
						$children[$tagname][$size]['@']
						= $vals[$i]['attributes'];
					}

					break;

				case 'close':
					return $children;
					break;
			}
		}

		return $children;
    }
}

