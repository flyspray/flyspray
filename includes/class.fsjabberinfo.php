<?php

/**
 * fsJabberInfo 
 * 
 * This class is a helper to expose
 * jabber capabilities to the user,otherwise
 * it will be a mistery for them
 * @package Flyspray
 * @version $Id$
 * @copyright 2006 Flsypray
 * @author Cristian Rodriguez <soporte@onfocus.cl> 
 * @license BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */

class fsJabberInfo {
   
    /**
     * can_use_jabber 
     *  if the use can actually use jabber or not
     * @var bool
     * @access public
     */
    var $can_use_jabber = false;
    /**
     * has_srv 
     *  if the user can make usage of SRV records support
     *  needed by gtalk and others.
     * @var mixed
     * @access public
     */
    var $has_srv = false;
    /**
     * has_ssl 
     * if the user can use SSL 
     * @var mixed
     * @access public
     */
    var $has_ssl = false;

    /**
     * has_tls 
     * if TLS can be used ( needed by gtalk and others)
     * @var mixed
     * @access public
     */
    var $has_tls = false;

    /**
     * has_digest 
     * if the user can make use of digest auth.
     * @var bool
     * @access public
     */
    var $has_digest = false;

 
    function fsJabberInfo()
    {
        $this->can_use_jabber = extension_loaded('xml');
        $this->has_ssl = extension_loaded('openssl');
        $this->has_digest = function_exists('mhash');
        //this is in short php 5.1 or later with openssl.
        $this->has_tls = ($this->has_ssl && function_exists('stream_socket_enable_crypto'));
        //this sadly means it only works in **linux** with php 5 or later
        //don't blame me. see http://php.net/manual/en/function.dns-get-record.php
        $this->has_srv = function_exists('dns_get_record');

    }

    /**
     * getInfo 
     *  return what ther use can do, in bidimensional array.
     * @access public
     * @return array
     */
    function getInfo()
    {
        $info = get_object_vars(&$this);
        $results = array();
        
        foreach($info as $property=>$value) {

            if($value === false) {

                $results['disabled'][] = $property;
            
            } else {

                $results['enabled'][] = $property;
            }

        }
            return $results;

    }

}
