<?php
// +----------------------------------------------------------------------
// | PHP Source                                                           
// +----------------------------------------------------------------------
// | Copyright (C) 2005 by Jeffery Fernandez <developer@jefferyfernandez.id.au>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//

if (!defined('VALID_FLYSPRAY')) die('Sorry you cannot access this file directly');

/**
 * Class to store information about Version and release
 * information of Helptickets.
 */
class Version
{
  /** @var string Product */
  var $mProductName;
  /** @var string Project unix name */
  var $mUnixName;
  /** @var int Main Release Level */
  var $mRelease;
  /** @var string Development Status */
  var $mDevStatus;
  /** @var int Sub Release Level */
  var $mDevLevel;
  /** @var string Release Codename */
  var $mCodename;
  /** @var string Release Date */
  var $mRelDate;
  /** @var string Release Time */
  var $mRelTime;
  /** @var string Timezone */
  var $mRelTimeZone;
  /** @var string Copyright Text */
  var $mCopyright;
  /** @var string Author */
  var $mAuthor;
  /** @var string mUrl */
  var $mUrl;

  var $mVersion = '';

  /**
   * Constructor for the class. Initialises Project version related info.
   */
  function Version()
  {
    // initialise values
    $this->mProductName = 'Flyspray';
    $this->mUnixName = 'flyspray';
    $this->mRelease = '0.9';
    $this->mDevStatus = 'devel';
    $this->mDevLevel = '8';
    $this->mCodename = 'Karate Kid';
    $this->mRelDate = '26/08/2005';
    $this->mRelTime = '10:00';
    $this->mRelTimeZone = 'GMT +10';
    $this->mCopyright = 'Copyright 2005 &copy; Tony Collins.  All rights reserved.';
    $this->mAuthor = 'Tony Collins';
    $this->mUrl = '<a href="http://flyspray.rocks.cc" title="Flyspray home page">Flyspray</a> is Free Software released under the GNU/GPL License.';
    $this->mVersion = $this->mProductName . ' ' . $this->mRelease . ".". $this->mDevLevel . " " . $this->mDevStatus . " [".$this->mCodename ."] " . $this->mRelDate . " " . $this->mRelTime . " " . $this->mRelTimeZone;
  }

}
?>
