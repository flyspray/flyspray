<?php

class Jabber
{
    var $connection = null;
    var $log = array();
    var $log_enabled = false;
    var $timeout = 10;
    var $ssl = false;
    var $tls = false;
    var $user = '';
    var $password = '';
    var $server = '';
    var $id = 0;
    var $auth = false;
    var $jid = null;
    var $session_req = false;
    
    function Jabber($login, $password, $ssl = false, $port = 5222)
    {
        // Can we use Jabber at all?
        // Note: Maybe replace with SimpleXML in the future
        if (!extension_loaded('xml')) {
            $this->log('Error: No XML functions available, Jabber functions can not operate.');
            return false;
        }
        
        // Extract data from user@server.org
        list($username, $server) = explode('@', $login);
        
        // Decide whether or not to use SSL
        $ssl = ($ssl && Jabber::can_use_ssl());
        $this->ssl      = $ssl;
        $this->server   = $server;
        $this->user     = $username;
        $this->password = $password;
        // Change port if we use SSL
        if ($port == 5222 && $ssl) {
            $port = 5223;
        }
        
        if ($this->open_socket($server, $port, $ssl)) {
            $this->send("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
			$this->send("<stream:stream to='{$server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");
        } else {
            return false;
        }
        // Now we listen what the server has to say...and give appropriate responses
        $this->listen();
    }

    /**
     * Send data to the Jabber server
     * @param string $xml
     * @access public
     * @return bool
     */
    function send($xml)
    {
        if (!feof($this->connection)) {
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
    function open_socket($server, $port, $ssl = false)
    {
        if (function_exists("dns_get_record")){
            $record = dns_get_record("_xmpp-client._tcp.$server", DNS_SRV);
            if (!empty($record)) {
                $server = $record[0]["target"];
            }
        }
        
        $server = $ssl ? 'ssl://' . $server : $server;

        if ($this->connection = @fsockopen($server, $port, $errorno, $errorstr)) {
            socket_set_blocking($this->connection, 0);
            socket_set_timeout($this->connection, 31536000);

            return true;
        }
        // Apparently an error occured...
        $this->log('Error: ' . $errorstr);
        return false;
    }
    
    function log($msg)
    {
        if ($this->log_enabled) {
            $this->log[] = $msg;
            return true;
        }
        
        return false;
    }
    
    function listen()
    {
        // Wait for a response until timeout is reached
        $start = time();
        $data = '';
        
        do {
            $data = trim(fread($this->connection, 4096));
        } while (time() <= $start + 10 && $data == '');
        
        if ($data != '') {
            // do a response
            $this->log('RECV: '. $data);
            $this->response(Jabber::xmlize($data));
        } else {
            $this->log('Timeout, no response from server.');
            return false;
        }    
    }
    
    function response($xml)
    {
        switch (key($xml)) {
            case 'stream:stream':
                // Connection initialised (or after authentication). Not much to do here...
                $this->id = $xml["stream:stream"][0]['@']['id'];
                if (isset($xml['stream:stream'][0]['#']['stream:features'])) {
                    // we already got all info we need
                    $this->response($xml['stream:stream'][0]['#']);
                } else {
                    $this->listen();
                }
                break;
            
            case 'stream:features':
                // Resource binding after successfull authentication
                if ($this->auth) {
                    if (isset($xml['stream:features'][0]['#']['session'])) {
                        // a session is required
                        $this->session_req = true;
                    }
                    $this->send("<iq type='set' id='bind_1'>
                                    <bind xmlns='urn:ietf:params:xml:ns:xmpp-bind'>
                                        <resource>class.jabber2.php</resource>
                                    </bind>
                                 </iq>");
                    $this->listen();
                    break;
                }
                // Let's use TLS if SSL is not enabled and we can actually use it
                if (!$this->ssl && Jabber::can_use_tls() && isset($xml['stream:features'][0]['#']['starttls'])) {
                    $this->log('Switching to TLS.');
                    $this->Send("<starttls xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>\n");
                    $this->listen();
                    return true;
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
                    } else if (in_array('PLAIN', $methods) && ($this->ssl || $this->tls)) {
                        $this->send("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>"
                                      . base64_encode(chr(0) . $this->user . '@' . $this->server . chr(0) . $this->password) .
                                    "</auth>");
                    // not good...
                    } else {
                        $this->log('Error: No authentication method supported.');
                        $this->disconnect();
                        return false;
                    }
                    $this->listen();
                    
                } else {
                    // ok, this is it. bye.
                    $this->log('Error: Server does not offer SASL authentication.');
                    $this->disconnect();
                    return false;
                }
                break;
            
            case 'challenge':
                // continue with authentication...a challenge literally -_-
                $decoded = base64_decode($xml['challenge'][0]['#'][0]);
                $decoded = Jabber::parse_data($decoded);
                // second challenge
                if (isset($decoded['rspauth'])) {
                    $this->send("<response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'/>");
                } else {
                    $response = array('username' => $this->user,
                                      'response' => $this->encrypt_password(array_merge($decoded, array('nc' => '00000001'))),
                                      'charset'  => 'utf-8',
                                      'nc'       => '00000001');
                    
                    foreach (array('nonce', 'qop', 'digest-uri', 'realm') as $key) {
                        if (isset($decoded[$key])) {
                            $response[$key] = $decoded[$key];
                        }
                    }

                    $this->send("<response xmlns='urn:ietf:params:xml:ns:xmpp-sasl'>" .
                                 base64_encode(Jabber::implode_data($response))
                                 . "</response>");
                }
                
                $this->listen();
                break;
            
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
                $this->tls = true;
                // new stream
                $this->send("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
                $this->send("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");
                
                $this->listen();
                break;
            
            case 'success':
                // Yay, authentication successfull.
                $this->send("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");
                $this->auth = true;
                $this->listen(); // we have to wait for another response
                break;
            
            case 'iq':
                // multiple possibilities here
                switch ($xml['iq'][0]['@']['id'])
                {
                    case 'bind_1':
                        $this->jid = $xml['iq'][0]['#']['bind'][0]['#']['jid'][0]['#'];
                        // and (maybe) yet another request to be able to send messages *finally*
                        if ($this->session_req) {
                            $this->send("<iq to='{$this->server}' 
                                             type='set'
                                             id='sess_1'>
                                          <session xmlns='urn:ietf:params:xml:ns:xmpp-session'/>
                                        </iq>");
                            $this->listen();
                        }
                        break;
                    
                    case 'sess_1':
                        break;
                    
                    default:
                        $this->log('Received unexpected IQ.');
                        break;
                }
                break;
                
            default:
                // hm...don't know this response
                $this->log('Error: Unknown server response (' . key($xml) . ')');
                break;
        }
    }
    
    function send_message($to, $text, $type = 'normal') {
        if (!$this->jid) {
            return false;
        }
        
        return $this->send("<message from='" . htmlspecialchars($this->jid) . "'
                                     to='" . htmlspecialchars($to) . "'
                                     xml:lang='en'
                                     type='" . htmlspecialchars($type) . "'
                                     id='" . uniqid('msg') . "'>
                              <body>" . htmlspecialchars($text) . "</body>
                            </message>");
    }
    
    function disconnect()
    {
        if (is_resource($this->connection)) {
            $this->Send('</stream:stream>');
            return fclose($this->connection);
        }
        return false;
    }
    
    function can_use_ssl()
    {
        return extension_loaded('openssl');
    }
    
    function can_use_tls()
    {
        return Jabber::can_use_ssl() && function_exists('stream_socket_enable_crypto');
    }
    
    /**
     * Encrypts a password as in RFC 2831
     * @param array $data Needs data from the client-server connection
     * @access public
     * @return string
     */
    function encrypt_password($data)
    {
        // let's me think about <challenge> again...      
        foreach (array('realm', 'cnonce', 'digest-uri') as $key) {
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }
        
        if (isset($data['authzid'])) {
            $a1 = pack('H*', md5($this->user . ':' . $data['realm'] . ':' . $this->password))  . ':' . $data['nonce'] . ':' . $data['cnonce'] . ':' . $data['authzid'];
        } else {
            $a1 = pack('H*', md5($this->user . ':' . $data['realm'] . ':' . $this->password))  . ':' . $data['nonce'] . ':' . $data['cnonce'];
        }

        // should be: qop = auth
        $a2 = 'AUTHENTICATE:'. $data['digest-uri'];

        return md5(md5($a1) . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . md5($a2));
    }
    
    /**
     * parse_data like a="b",c="d",... 
     * @param string $data
     * @access public
     * @return array a => b ...
     */
    function parse_data($data)
    {
        // super basic, but should suffice
        $data = explode(',', $data);
        $pairs = array();
        foreach ($data as $pair) {
            $pair = explode('=', $pair);
            if (count($pair) == 2) {
                $pairs[$pair[0]] = trim($pair[1], '"');
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
    function implode_data($data)
    {
        $return = array();
        foreach ($data as $key => $value) {
            $return[] = $key . '="' . $value . '"';
        }
        return implode(',', $return);
    }
    
    // ======================================================================
	// Third party code, taken from old jabber lib
	// ======================================================================

	// xmlize()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/

	function xmlize($data, $WHITE=1, $encoding='UTF-8') {

		$data = trim($data);
		$vals = $index = $array = array();
		$parser = xml_parser_create($encoding);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $WHITE);
		xml_parse_into_struct($parser, $data, $vals, $index);
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


		return $array;
	}



	// _xml_depth()
	// (c) Hans Anderson / http://www.hansanderson.com/php/xml/

	function _xml_depth($vals, &$i) {
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

?>
