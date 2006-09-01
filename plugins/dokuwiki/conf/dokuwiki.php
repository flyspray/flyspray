<?php
/**
 * This is DokuWiki's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 * For help with the configuration see http://www.splitbrain.org/dokuwiki/wiki:config
 */


/* Datastorage and Permissions */

$doku_conf['umask']       = 0111;              //set the umask for new files
$doku_conf['dmask']       = 0000;              //directory mask accordingly
$doku_conf['lang']        = 'en';              //your language
$doku_conf['basedir']     = '';                //absolute dir from serveroot - blank for autodetection
$doku_conf['baseurl']     = '';                //URL to server including protocol - blank for autodetect
$doku_conf['savedir']     = './data';          //where to store all the files

/* Display Options */

$doku_conf['start']       = 'start';           //name of start page
$doku_conf['title']       = 'DokuWiki';        //what to show in the title
$doku_conf['template']    = 'default';         //see tpl directory
$doku_conf['fullpath']    = 0;                 //show full path of the document or relative to datadir only? 0|1
$doku_conf['recent']      = 20;                //how many entries to show in recent
$doku_conf['breadcrumbs'] = 10;                //how many recent visited pages to show
$doku_conf['typography']  = 1;                 //convert quotes, dashes and stuff to typographic equivalents? 0|1
$doku_conf['htmlok']      = 0;                 //may raw HTML be embedded? This may break layout and XHTML validity 0|1
$doku_conf['phpok']       = 0;                 //may PHP code be embedded? Never do this on the internet! 0|1
$doku_conf['dformat']     = 'Y/m/d H:i';       //dateformat accepted by PHPs date() function
$doku_conf['signature']   = ' --- //[[@MAIL@|@NAME@]] @DATE@//'; //signature see wiki:config for details
$doku_conf['toptoclevel'] = 1;                 //Level starting with and below to include in AutoTOC (max. 5)
$doku_conf['maxtoclevel'] = 3;                 //Up to which level include into AutoTOC (max. 5)
$doku_conf['maxseclevel'] = 3;                 //Up to which level create editable sections (max. 5)
$doku_conf['camelcase']   = 0;                 //Use CamelCase for linking? (I don't like it) 0|1
$doku_conf['deaccent']    = 1;                 //convert accented chars to unaccented ones in pagenames?
$doku_conf['useheading']  = 0;                 //use the first heading in a page as its name
$doku_conf['refcheck']    = 1;                 //check for references before deleting media files
$doku_conf['refshow']     = 0;                 //how many references should be shown, 5 is a good value

/* Antispam Features */

$doku_conf['usewordblock']= 1;                 //block spam based on words? 0|1
$doku_conf['indexdelay']  = 60*60*24*5;        //allow indexing after this time (seconds) default is 5 days
$doku_conf['relnofollow'] = 1;                 //use rel="nofollow" for external links?
$doku_conf['mailguard']   = 'hex';             //obfuscate email addresses against spam harvesters?
                                          //valid entries are:
                                          //  'visible' - replace @ with [at], . with [dot] and - with [dash]
                                          //  'hex'     - use hex entities to encode the mail address
                                          //  'none'    - do not obfuscate addresses

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$doku_conf['useacl']      = 0;                //Use Access Control Lists to restrict access?
$doku_conf['openregister']= 1;                //Should users to be allowed to register?
$doku_conf['autopasswd']  = 1;                //autogenerate passwords and email them to user
$doku_conf['authtype']    = 'plain';          //which authentication backend should be used
$doku_conf['passcrypt']   = 'smd5';           //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$doku_conf['defaultgroup']= 'user';           //Default groups new Users are added to
$doku_conf['superuser']   = '!!not set!!';    //The admin can be user or @group

/* Advanced Options */
$doku_conf['userewrite']  = 0;                //this makes nice URLs: 0: off 1: .htaccess 2: internal
$doku_conf['useslash']    = 0;                //use slash instead of colon? only when rewrite is on
$doku_conf['sepchar']     = '_';              //word separator character in page names; may be a
                                         //  letter, a digit, '_', '-', or '.'.
$doku_conf['canonical']   = 0;                //Should all URLs use full canonical http://... style?
$doku_conf['autoplural']  = 0;                //try (non)plural form of nonexisting files?
$doku_conf['usegzip']     = 1;                //gzip old revisions?
$doku_conf['cachetime']   = 60*60*24;         //maximum age for cachefile in seconds (defaults to a day)
$doku_conf['purgeonadd']  = 1;                //purge cache when a new file is added (needed for up to date links)
$doku_conf['locktime']    = 15*60;            //maximum age for lockfiles (defaults to 15 minutes)
$doku_conf['notify']      = '';               //send change info to this email (leave blank for nobody)
$doku_conf['mailfrom']    = '';               //use this email when sending mails
$doku_conf['gdlib']       = 2;                //the GDlib version (0, 1 or 2) 2 tries to autodetect
$doku_conf['im_convert']  = '';               //path to ImageMagicks convert (will be used instead of GD)
$doku_conf['spellchecker']= 0;                //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$doku_conf['subscribers'] = 0;                //enable change notice subscription support
$doku_conf['pluginmanager'] = 0;              //enable automated plugin management (requires plugin)
$doku_conf['rss_type']    = 'rss1';           //type of RSS feed to provide, by default:
                                         //  'rss'  - RSS 0.91
                                         //  'rss1' - RSS 1.0
                                         //  'rss2' - RSS 2.0
                                         //  'atom' - Atom 0.3
$doku_conf['rss_linkto'] = 'diff';            //what page RSS entries link to:
                                         //  'diff'    - page showing revision differences
                                         //  'page'    - the revised page itself
                                         //  'rev'     - page showing all revisions
                                         //  'current' - most recent revision of page

//Set target to use when creating links - leave empty for same window
$doku_conf['target']['wiki']      = '';
$doku_conf['target']['interwiki'] = '';
$doku_conf['target']['extern']    = '';
$doku_conf['target']['media']     = '';
$doku_conf['target']['windows']   = '';

//Proxy setup - if your Server needs a proxy to access the web set these
$doku_conf['proxy']['host'] = '';
$doku_conf['proxy']['port'] = '';
$doku_conf['proxy']['user'] = '';
$doku_conf['proxy']['pass'] = '';
$doku_conf['proxy']['ssl']  = 0;

/* Safemode Hack */
$doku_conf['safemodehack'] = 0;               //read http://wiki.splitbrain.org/wiki:safemodehack !
$doku_conf['ftp']['host'] = 'localhost';
$doku_conf['ftp']['port'] = '21';
$doku_conf['ftp']['user'] = 'user';
$doku_conf['ftp']['pass'] = 'password';
$doku_conf['ftp']['root'] = '/home/user/htdocs';

