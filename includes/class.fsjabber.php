<?php

/**
 * fsJabber 
 *  This class corrects the bad behaviuors of jabber.class.php
 *  and make it the work suiting the flyspray needs
 * @package Flyspray
 * @version $Id$
 * @copyright 2006 Flyspray
 * @author Cristian Rodriguez <soporte@onfocus.cl> 
 * @license LGPL
 */

require_once dirname(__FILE__) . '/external/class.jabber.php';

class fsJabber extends Jabber {

    var $ssl = false;

    function fsJabber()
    {
        parent::Jabber();
        //replace the base connector class.
        $this->connection_class = 'fsJabberConnector';
    }

   /**
    * Connect 
    * replace Base class Connect() method
    * @access public
    * @return bool
    */
   function Connect() {

        $this->_create_logfile();

        $this->CONNECTOR = new $this->connection_class;

        if ($this->CONNECTOR->OpenSocket($this->server, $this->port, $this->_useSSL())) {
            $this->SendPacket("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
            $this->SendPacket("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' version='1.0'>\n");

            sleep(2);

            if ($this->_check_connected()) {
                $this->connected = true;    // Nathan Fritz
                return true;
            } else {
                $this->AddToLog("ERROR: Connect() #1");
                return false;
            }
        } else {
            $this->AddToLog("ERROR: Connect() #2");
            return FALSE;
        }
    }
 
    /**
     * _array_htmlspecialchars 
     * replaces completely non-functional stuff provided
     * by the base class
     * @param array $array 
     * @access protected
     * @return array
     */

    function _array_htmlspecialchars($array)
    {
        if (is_array($array))
        {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    $array[$k] = $this->_array_htmlspecialchars($v);
                } else {
                    $array[$k] = htmlspecialchars($v, ENT_QUOTES, 'utf-8');
                }
            }
        }

        return $array;
    }
 
    /**
     * replaces upstream AddToLog 
     * 
     * @param mixed $string 
     * @access public
     * @return void
     */
    function AddToLog($string)
    {
        if ($this->enable_logging) {

            if (is_resource($this->log_filehandler)) {
                @fwrite($this->log_filehandler, $string . "\n\n");
            }           
        } else {
                $this->log_array[] = htmlspecialchars($string, ENT_QUOTES, 'utf-8');
        }
    }
    
    function _starttls()
    {
        if (!extension_loaded('openssl')) {

            $this->AddToLog("WARNING: TLS is not available: no SSL support in PHP");
            return true;
        }
            return parent::_starttls();
 
    /**
     * _useSSL 
     * 
     * We use ssl or not eh ?
     * @access protected
     * @return bool
     */
    function _useSSL()
    {
        return ($this->ssl && extension_loaded('openssl'));
    }
}


/**
 * fsJabberConnector 
 *  replaces a part of the jabber connector class
 *  to make it work properly.
 * @uses CJP
 * @uses _StandardConnector
 * @package Flsypray
 * @version $Id$
 * @copyright 2006 Flyspray
 * @author Cristian Rodriguez <soporte@onfocus.cl> 
 * @license LGPL
 */
class fsJabberConnector extends CJP_StandardConnector {

    /**
     * OpenSocket 
     *  replaces OpenSocket() 
     * @param string $server host to connect to
     * @param int $port port number
     * @param bool $ssl use ssl or not  
     * @access public
     * @return bool
     */
    function OpenSocket($server, $port, $ssl = false)
    {
        if (function_exists("dns_get_record")){
            $record = dns_get_record("_xmpp-client._tcp.$server", DNS_SRV);
            if (!empty($record)) {
                $server = $record[0]["target"];
                $port = $record[0]["port"];
            }
        }
        
        $server = $ssl ? 'ssl://' . $server : $server;

        if ($this->active_socket = @fsockopen($server, $port)) {

            socket_set_blocking($this->active_socket, 0);
            socket_set_timeout($this->active_socket, 31536000);

            return true;
        
        } else {

           return false;
        }
    }    
}
