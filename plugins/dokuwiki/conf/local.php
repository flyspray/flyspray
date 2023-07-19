<?php

/**
 * dokuwiki settings for Flyspray project, task, and comment descriptions
 */

$conf['sepchar'] = '_';
$conf['useheading'] = 0;
$conf['useslash'] = 0;
$conf['deaccent'] = 1;
$conf['toptoclevel']=2;
$conf['maxtoclevel']=0;
$conf['maxseclevel']=0;

$conf['target']['wiki']      = '';
$conf['target']['interwiki'] = '';
$conf['target']['extern']    = '';
$conf['target']['media']     = '';
$conf['target']['windows']   = '';

#
$conf['typography'] = 0;
$conf['camelcase'] = 0;
$conf['savedir'] = './data'; # just to silence unset warning
$conf['lockdir'] = $conf['savedir']; # just to silence unset warning
