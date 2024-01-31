<?php

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

class Tpl
{
	public $_uses  = array();
	public $_vars  = array();
	public $_theme = '';
	public $_tpls  = array();
	public $_title = "";

	public function uses()
	{
		$args = func_get_args();
		$this->_uses = array_merge($this->_uses, $args);
	}

	public function assign($arg0 = null, $arg1 = null)
	{
		if (is_string($arg0)) {
			$this->_vars[$arg0] = $arg1;
		}elseif (is_array($arg0)) {
			$this->_vars += $arg0;
		}elseif (is_object($arg0)) {
			$this->_vars += get_object_vars($arg0);
		}
	}

	public function getTheme()
	{
		return $this->_theme;
	}

	public function setTheme($theme)
	{
		// Check available themes
		$theme = trim($theme, '/');
		$themes = Flyspray::listThemes();
		if (in_array($theme, $themes)) {
			$this->_theme = $theme.'/';
		} else {
			$this->_theme = $themes[0].'/';
		}
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}

	public function themeUrl()
	{
		return sprintf('%sthemes/%s', $GLOBALS['baseurl'], $this->_theme);
	}

	public function pushTpl($_tpl)
	{
		$this->_tpls[] = $_tpl;
	}

	public function catch_start()
	{
		ob_start();
	}

	public function catch_end()
	{
		$this->_tpls[] = array(ob_get_contents());
		ob_end_clean();
	}

	public function display($_tpl, $_arg0 = null, $_arg1 = null)
	{
		// if only plain text
		if (is_array($_tpl) && count($tpl)) {
			echo $_tpl[0];
			return;
		}

		// variables part
		if (!is_null($_arg0)) {
			$this->assign($_arg0, $_arg1);
		}

		foreach ($this->_uses as $_var) {
			global $$_var;
		}

		extract($this->_vars, EXTR_REFS|EXTR_SKIP);

		if (is_readable(BASEDIR . '/themes/' . $this->_theme.'templates/'.$_tpl)) {
			require BASEDIR . '/themes/' . $this->_theme.'templates/'.$_tpl;
		} elseif (is_readable(BASEDIR . '/themes/CleanFS/templates/'.$_tpl)) {
			# if a custom theme folder only contains a fraction of the .tpl files, use the template of the default full theme as fallback.
			require BASEDIR . '/themes/CleanFS/templates/'.$_tpl;
		} else {
			# This is needed to catch times when there is no theme (for example setup pages, where BASEDIR is ../setup/  not ../)
			require BASEDIR . "/templates/".$_tpl;
		}
	}

	public function render()
	{
		while (count($this->_tpls)) {
			$this->display(array_shift($this->_tpls));
		}
	}

	public function fetch($tpl, $arg0 = null, $arg1 = null)
	{
		ob_start();
		$this->display($tpl, $arg0, $arg1);
		return ob_get_clean();
	}
}

class FSTpl extends Tpl
{
    public $_uses = array('fs', 'conf', 'baseurl', 'language', 'proj', 'user');

    public function get_image($name, $base = true)
	{
        global $proj, $baseurl;
        $pathinfo = pathinfo($name);
        $link = sprintf('themes/%s/', $proj->prefs['theme_style']);
        if ($pathinfo['dirname'] != '.') {
            $link .= $pathinfo['dirname'] . '/';
            $name = $pathinfo['basename'];
        }

        $extensions = array('.png', '.gif', '.jpg', '.ico');

        foreach ($extensions as $ext) {
            if (is_file(BASEDIR . '/' . $link . $name . $ext)) {
                return ($base) ? ($baseurl . $link . $name . $ext) : ($link . $name . $ext);
            }
        }
        return '';
    }

}

/**
 * Draws the form start tag and the important anticsrftoken on 'post'-forms
 *
 * @param string action
 * @param string name optional attribute of form tag
 * @param string method optional request method, default 'post'
 * @param string enctype optional enctype, default 'multipart/form-data'
 * @param string attr optional attributes for the form tag, example: 'id="myformid" class="myextracssclass"'
 *
 * @return string
 */
function tpl_form($action, $name=null, $method=null, $enctype=null, $attr='')
{
        global $baseurl;

        if (null === $method) {
                $method='post';
        }
        if (null === $enctype) {
                $enctype='multipart/form-data';
        }

        if(substr($action,0,4)!='http'){$action=$baseurl.$action;}
        return '<form action="'.$action.'"'.($method=='get'?' method="get"':' method="post"').
                ( $name!='' ? ' name="'.$name.'"':'').
                ( ' enctype="'.$enctype.'"').
                ( ' '.$attr).'>'.
                ( $method=='post' ? '<input type="hidden" name="csrftoken" value="'.$_SESSION['csrftoken'].'" />':'');
}

/**
 * Creates a link to a task
 *
 * @param array task with properties of a task. It also accepts a task_id, but that requires extra queries executed by this function.
 * @param string text optional, by default the FS# + summary of task is used.
 * @param bool strict check task permissions by the function too. Extra SQL queries if set true. default false.
 * @param array attr extra attributes
 * @param array title informations shown when hover over the link (title attribute of the HTML a-tag)
 *
 * @return string ready for html output
 */
function tpl_tasklink($task, $text = null, $strict = false, $attrs = array(), $title = array('status','summary','percent_complete'))
{
    global $user;

    $params = array();

    if (!is_array($task) || !isset($task['status_name'])) {
        $td_id = (is_array($task) && isset($task['task_id'])) ? $task['task_id'] : $task;
        $task = Flyspray::getTaskDetails($td_id, true);
    }

    if ($strict === true && (!is_object($user) || !$user->can_view_task($task))) {
        return '';
    }

    if (is_object($user) && $user->can_view_task($task)) {
        $summary = utf8_substr($task['item_summary'], 0, 64);
    } else {
        $summary = L('taskmadeprivate');
    }

    if (is_null($text)) {
        $text = sprintf('FS#%d - %s', $task['task_id'], Filters::noXSS($summary));
    } elseif(is_string($text)) {
        $text = htmlspecialchars(utf8_substr($text, 0, 64), ENT_QUOTES, 'utf-8');
    } else {
        //we can't handle non-string stuff here.
        return '';
    }

    if (!$task['task_id']) {
        return $text;
    }

    $title_text = array();

    foreach($title as $info)
    {
        switch($info)
        {
            case 'status':
                if ($task['is_closed']) {
                    $title_text[] = $task['resolution_name'];
                    $attrs['class'] = 'closedtasklink';
                } else {
                    $title_text[] = $task['status_name'];
                }
                break;

            case 'summary':
                $title_text[] = $summary;
                break;

            case 'assignedto':
                if (isset($task['assigned_to_name']) ) {
                    if (is_array($task['assigned_to_name'])) {
                        $title_text[] = implode(', ', $task['assigned_to_name']);
                    } else {
                        $title_text[] = $task['assigned_to_name'];
                    }
                }
                break;

            case 'percent_complete':
                    $title_text[] = $task['percent_complete'].'%';
                break;

            case 'category':
                if ($task['product_category']) {
                    if (!isset($task['category_name'])) {
                        $task = Flyspray::getTaskDetails($task['task_id'], true);
                    }
                    $title_text[] = $task['category_name'];
                }
                break;

            // ... more options if necessary
        }
    }

    $title_text = implode(' | ', $title_text);

    // to store search options
    $params = $_GET;
	unset($params['do'], $params['action'], $params['task_id'], $params['switch']);
	if(isset($params['event_number'])){
		# shorter links to tasks from report page
		unset($params['events'], $params['event_number'], $params['fromdate'], $params['todate'], $params['submit']);
	}

	# We can unset the project param for shorter urls because flyspray knows project_id from current task data.
	# Except we made a search from an 'all projects' view before, so the prev/next navigation on details page knows
	# if it must search only in the project of current task or all projects the user is allowed to see tasks.
	if(!isset($params['advancedsearch']) || (isset($params['project']) && $params['project']!=0) ){
		unset($params['project']);
	}

    $url = htmlspecialchars(createURL('details', $task['task_id'],  null, $params), ENT_QUOTES, 'utf-8');
    $title_text = htmlspecialchars($title_text, ENT_QUOTES, 'utf-8');
    $link  = sprintf('<a href="%s" title="%s" %s>%s</a>',$url, $title_text, join_attrs($attrs), $text);

    if ($task['is_closed']) {
        $link = '<del>&#160;' . $link . '&#160;</del>';
    }
    return $link;
}

/**
 * Creates a textlink to a user profile.
 *
 * @param int|array $uid user_id or a simple array(user_id, user_name, real_name) from {users} db table
 *
 * @since 1.0-rc10 Changed displaying user_name instead real_name because username is unique for a Flyspray installation while there could be many 'John Smith'.
 *                 Also there are cases real_name is just empty string in database, so the link was not visible.
 *                 The real_name is now in the title attribute of the link so simply hover shows what's the users chosen real_name or accessed by CSS.
 *                 Also makes creating a '@mention'-plugin possible as people know quickly how to link someone in a task description or comment.
 *
 * @since 0.9.9.7 Changed the display from 'real_name (user_name)' to just 'real_name'.
 *
 * @see tpl_userlinkavatar() for a link with user avatar icon
 */
function tpl_userlink($uid)
{
	global $db, $user;

	static $cache = array();

	if (is_array($uid)) {
		list($uid, $uname, $rname) = $uid;
	} elseif (empty($cache[$uid])) {
		$sql = $db->query('SELECT user_name, real_name FROM {users} WHERE user_id = ?',
                           array(intval($uid)));
		if ($sql && $db->countRows($sql)) {
			list($uname, $rname) = $db->fetchRow($sql);
		}
	}

	if (isset($uname)) {
		$url = createURL('user', $uid);
		$cache[$uid] = vsprintf('<a href="%s" title="%s">%s</a>', array_map(array('Filters', 'noXSS'), array($url, $rname, $uname)));
	} elseif (empty($cache[$uid])) {
		$cache[$uid] = eL('anonymous');
	}

	return $cache[$uid];
}

/**
* turns a @username or just username into a link to the flyspray user profile
* intended to be used by dokuwiki plugin flyspray user mention
*
* @param string $username
*
* @return string
*/
function tpl_mentionlink($username)
{
	global $db, $user;

	if (is_string($username)) {
		if (substr($username, 0, 1)=='@') {
			$uname=substr($username, 1);
		} else {
			$uname=$username;
		}
		$sql = $db->query(
			'SELECT user_id, user_name, real_name FROM {users} WHERE user_name LIKE ?',
			array($uname));
		if ($sql && $db->countRows($sql)) {
			list($uid,$uname, $rname) = $db->fetchRow($sql);
			$url = createURL('user', $uid);
			return vsprintf(
				'<a href="%s" class="ulink">%s</a>',
				array_map(array('Filters', 'noXSS'), array($url, '@'.$uname))
			);
		}
		# return unchanged as $username only contains /@*[0-9a-zA-Z]+/
		return $username;
	}
}

/**
* Builds the HTML string for displaying a gravatar image or an uploaded user image.
* The string for a user and a size is cached per request.
*
* Class and style parameter should be avoided to make this function more effective for caching (less SQL queries)
*
* @param int uid the id of the user
* @param int size in pixel for displaying. Should use global max_avatar_size pref setting by default.
* @param string class optional, avoid calling with class parameter for better 'cacheability'
* @param string style optional, avoid calling with style parameter for better 'cacheability'
*/
function tpl_userlinkavatar($uid, $size, $class='', $style='')
{
	global $db, $user, $baseurl, $fs;

	static $avacache=array();

	if( !($uid>0) ){
                return '<i class="fa fa-user"></i>';
        }

	if($uid>0 && (empty($avacache[$uid]) || !isset($avacache[$uid][$size]))){
		if (!isset($avacache[$uid]['uname'])) {
			$sql = $db->query('SELECT user_name, real_name, email_address, profile_image FROM {users} WHERE user_id = ?', array(intval($uid)));
			if ($sql && $db->countRows($sql)) {
				list($uname, $rname, $email, $profile_image) = $db->fetchRow($sql);
			} else {
				return;
			}
			$avacache[$uid]['profile_image'] = $profile_image;
			$avacache[$uid]['uname'] = $uname;
			$avacache[$uid]['rname'] = $rname;
			$avacache[$uid]['email'] = $email;
		}

		if (is_file(BASEDIR.'/avatars/'.$avacache[$uid]['profile_image'])) {
			$image = '<img src="'.$baseurl.'avatars/'.$avacache[$uid]['profile_image'].'"/>';
		} else {
			if (isset($fs->prefs['gravatars']) && $fs->prefs['gravatars'] == 1) {
				$email = md5(strtolower(trim($avacache[$uid]['email'])));
				$default = 'mm';
				$imgurl = '//www.gravatar.com/avatar/'.$email.'?d='.urlencode($default).'&s='.$size;
				$image = '<img src="'.$imgurl.'"/>';
			} else {
				$image = '<i class="fa fa-user" style="font-size:'.$size.'px"></i>';
			}
		}
		if (isset($avacache[$uid]['uname'])) {
			#$url = createURL(($user->perms('is_admin')) ? 'edituser' : 'user', $uid);
			# peterdd: I think it is better just to link to the user's page instead direct to the 'edit user' page also for admins.
			# With more personalisation coming (personal todo list, charts, ..) in future to flyspray
			# the user page itself is of increasing value. Instead show the 'edit user'-button on user's page.
			$url = createURL('user', $uid);
			$avacache[$uid][$size] = '<a'.($class!='' ? ' class="'.$class.'"':'').($style!='' ? ' style="'.$style.'"':'').' href="'.$url.'" title="'.Filters::noXSS($avacache[$uid]['rname']).'">'.$image.'</a>';
		}
	}
	return $avacache[$uid][$size];
}

function tpl_fast_tasklink($arr)
{
    return tpl_tasklink($arr[1], $arr[0]);
}

/**
 * Formats a task tag for HTML output based on a global $alltags array
 *
 * @param int $id tag_id of {list_tag} db table
 * @param bool $showid set true if the tag_id is shown instead of the tag_name
 * @param int $added for task details view
 * @param int $addedby for task details view
 *
 * @return string ready for output
 */
function tpl_tag($id, $showid=false, $added=null, $addedby=null)
{
	global $alltags;

	if(!is_array($alltags)) {
		$alltags=Flyspray::getAllTags();
	}

	if(isset($alltags[$id])){
		$out='<i class="tag t'.$id;
		if( isset($alltags[$id]['class']) && preg_match('/^#([0-9a-f]{3}){1,2}$/i', $alltags[$id]['class']) ) {
			$out.= '" style="background-color:'.$alltags[$id]['class'];
			# max only calc once per tag per request
			# assumes theme css of default tag font color is #000
			if(!isset($alltags[$id]['fcolor'])){
				$bg = hex2RGB($alltags[$id]['class']);
				# from https://www.w3.org/TR/AERT/#color-contrast
				$brightness=(299*$bg['r'] + 587*$bg['g'] + 114*$bg['b']) / 1000;
				if($brightness<126){
					$out.=';color:#fff';
					$alltags[$id]['fcolor']='#fff';
				}else{
					$alltags[$id]['fcolor']='';
				}
			} else if( $alltags[$id]['fcolor']==='#fff'){
				$out.=';color:#fff';
			}
			$out.='"';
		} else {
			$out.= (isset($alltags[$id]['class']) ? ' '.htmlspecialchars($alltags[$id]['class'], ENT_QUOTES, 'utf-8') : '').'"';
		}

		if (is_null($added) && is_null($addedby)) {
			if ($showid) {
				$out.='>'.$id.'</i>';
			} else {
				$out.=' title="'.htmlspecialchars($alltags[$id]['tag_name'], ENT_QUOTES, 'utf-8').'"></i>';
			}
		} else {
			# task details view contains more details
			$out .= '>';
			$out .= htmlspecialchars($alltags[$id]['tag_name'], ENT_QUOTES, 'utf-8');
			if ($added>0) {
				$out .= '<span class="added">'.formatDate($added).'</span>';
			}
			if ($addedby>0) {
				$out .= '<span class="addedby">'.tpl_userlink($addedby).'</span>';
			}
			$out .= '</i>';
		}
		return $out;
	}
}

/**
* Convert a hexa decimal color code to its RGB equivalent
*
* used by tpl_tag()
*
* @param string $hexstr (hexadecimal color value)
* @param boolean $returnasstring (if set true, returns the value separated by the separator character. Otherwise returns associative array)
* @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
* @return array or string (depending on second parameter. Returns False if invalid hex color value)
*
* function is adapted from an exmaple on http://php.net/manual/de/function.hexdec.php
*/
function hex2RGB($hexstr, $returnasstring = false, $seperator = ',') {
    $hexstr = preg_replace("/[^0-9A-Fa-f]/", '', $hexstr); // Gets a proper hex string
    $rgb = array();
    if (strlen($hexstr) == 6) { // if a proper hex code, convert using bitwise operation. No overhead... faster
        $colorval = hexdec($hexstr);
        $rgb['r'] = 0xFF & ($colorval >> 0x10);
        $rgb['g'] = 0xFF & ($colorval >> 0x8);
        $rgb['b'] = 0xFF & $colorval;
    } elseif (strlen($hexstr) == 3) { // if shorthand notation, need some string manipulations
        $rgb['r'] = hexdec(str_repeat(substr($hexstr, 0, 1), 2));
        $rgb['g'] = hexdec(str_repeat(substr($hexstr, 1, 1), 2));
        $rgb['b'] = hexdec(str_repeat(substr($hexstr, 2, 1), 2));
    } else {
	return false; // invalid hex color code
    }
    return $returnasstring ? implode($seperator, $rgb) : $rgb; // returns the rgb string or the associative array
}

/**
 * joins an array of tag attributes together for output in a HTML tag.
 *
 * @param array attr
 *
 * @return string
 */
function join_attrs($attr = null) {
    if (is_array($attr) && count($attr)) {
        $arr = array();
        foreach ($attr as $key=>$val) {
            $arr[] = vsprintf('%s = "%s"', array_map(array('Filters', 'noXSS'), array($key, $val)));
        }
        return ' '.join(' ', $arr);
    }
    return '';
}

/**
 * Datepicker
 */
function tpl_datepicker($name, $label = '', $value = 0) {
    global $user, $page;

    $date = '';

    if ($value) {
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }

        if (!$user->isAnon()) {
            $st = date('Z')/3600; // server GMT timezone
            $value += ($user->infos['time_zone'] - $st) * 60 * 60;
        }

        $date = date('Y-m-d', intval($value));

     /* It must "look" as a date..
      * XXX : do not blindly copy this code to validate other dates
      * this is mostly a tongue-in-cheek validation
      * 1. it will fail on 32 bit systems on dates < 1970
      * 2. it will produce different results bewteen 32 and 64 bit systems for years < 1970
      * 3. it will not work when year > 2038 on 32 bit systems (see http://en.wikipedia.org/wiki/Year_2038_problem)
      *
      * Fortunately tasks are never opened to be dated on 1970 and maybe our sons or the future flyspray
      * coders may be willing to fix the 2038 issue ( in the strange case 32 bit systems are still used by that year) :-)
      */

    } elseif (Req::has($name) && strlen(Req::val($name))) {

        //strtotime sadly returns -1 on faliure in php < 5.1 instead of false
        $ts = strtotime(Req::val($name));

        foreach (array('m','d','Y') as $period) {
            //checkdate only accepts arguments of type integer
            $$period = intval(date($period, $ts));
        }
        // $ts has to be > 0 to get around php behavior change
        // false is casted to 0 by the ZE
        $date = ($ts > 0 && checkdate($m, $d, $Y)) ? Req::val($name) : '';
    }


    $subPage = new FSTpl;
    $subPage->setTheme($page->getTheme());
    $subPage->assign('name', $name);
    $subPage->assign('date', $date);
    $subPage->assign('label', $label);
    $subPage->assign('dateformat', '%Y-%m-%d');
    $subPage->display('common.datepicker.tpl');
}

/**
 * user selector
 */
function tpl_userselect($name, $value = null, $id = '', $attrs = array()) {
    global $db, $user, $proj;

    if (!$id) {
        $id = $name;
    }

    if ($value && ctype_digit($value)) {
        $sql = $db->query('SELECT user_name FROM {users} WHERE user_id = ?', array($value));
        $value = $db->fetchOne($sql);
    }

    if (!$value) {
        $value = '';
    }


    $page = new FSTpl;
    $page->setTheme($proj->prefs['theme_style']);
    $page->assign('name', $name);
    $page->assign('id', $id);
    $page->assign('value', $value);
    $page->assign('attrs', $attrs);
    $page->display('common.userselect.tpl');
}

/**
 * Creates the options for a date format select
 *
 * @selected The format that should by selected by default
 * @return html formatted options for a select tag
**/
function tpl_date_formats($selected, $detailed = false)
{
	$time = time();

	# TODO: rewrite using 'return tpl_select(...)'
	if (!$detailed) {
		$dateFormats = array(
			'%d.%m.%Y' => strftime('%d.%m.%Y', $time).' (DD.MM.YYYY)', # popular in many european countries
			'%d/%m/%Y' => strftime('%d/%m/%Y', $time).' (DD/MM/YYYY)', # popular in Greek
			'%m/%d/%Y' => strftime('%m/%d/%Y', $time).' (MM/DD/YYYY)', # popular in USA

			'%d.%m.%y' => strftime('%d.%m.%y', $time),

			'%Y.%m.%d' => strftime('%Y.%m.%d', $time),
			'%y.%m.%d' => strftime('%y.%m.%d', $time),

			'%d-%m-%Y' => strftime('%d-%m-%Y', $time),
			'%d-%m-%y' => strftime('%d-%m-%y', $time),

			'%Y-%m-%d' => strftime('%Y-%m-%d', $time).' (YYYY-MM-DD, ISO 8601)',
			'%y-%m-%d' => strftime('%y-%m-%d', $time),

			'%d %b %Y' => strftime('%d %b %Y', $time),
			'%d %B %Y' => strftime('%d %B %Y', $time),

			'%b %d %Y' => strftime('%b %d %Y', $time),
			'%B %d %Y' => strftime('%B %d %Y', $time),
		);
	}
	else {
		# TODO: maybe use optgroups for tpl_select() to separate 24h and 12h (am/pm) formats
		$dateFormats = array(
			'%d.%m.%Y %H:%M' 	=> strftime('%d.%m.%Y %H:%M', $time),
			'%d.%m.%y %H:%M' 	=> strftime('%d.%m.%y %H:%M', $time),

			'%d.%m.%Y %I:%M %p' => strftime('%d.%m.%Y %I:%M %p', $time),
			'%d.%m.%y %I:%M %p' => strftime('%d.%m.%y %I:%M %p', $time),

			'%Y.%m.%d %H:%M' 	=> strftime('%Y.%m.%d %H:%M', $time),
			'%y.%m.%d %H:%M' 	=> strftime('%y.%m.%d %H:%M', $time),

			'%Y.%m.%d %I:%M %p' => strftime('%Y.%m.%d %I:%M %p', $time),
			'%y.%m.%d %I:%M %p' => strftime('%y.%m.%d %I:%M %p', $time),

			'%d-%m-%Y %H:%M' 	=> strftime('%d-%m-%Y %H:%M', $time),
			'%d-%m-%y %H:%M' 	=> strftime('%d-%m-%y %H:%M', $time),

			'%d-%m-%Y %I:%M %p' => strftime('%d-%m-%Y %I:%M %p', $time),
			'%d-%m-%y %I:%M %p' => strftime('%d-%m-%y %I:%M %p', $time),

			'%Y-%m-%d %H:%M' 	=> strftime('%Y-%m-%d %H:%M', $time),
			'%y-%m-%d %H:%M' 	=> strftime('%y-%m-%d %H:%M', $time),

			'%Y-%m-%d %I:%M %p' => strftime('%Y-%m-%d %I:%M %p', $time),
			'%y-%m-%d %I:%M %p' => strftime('%y-%m-%d %I:%M %p', $time),

			'%d %b %Y %H:%M' 	=> strftime('%d %b %Y %H:%M', $time),
			'%d %B %Y %H:%M' 	=> strftime('%d %B %Y %H:%M', $time),

			'%d %b %Y %I:%M %p' => strftime('%d %b %Y %I:%M %p', $time),
			'%d %B %Y %I:%M %p' => strftime('%d %B %Y %I:%M %p', $time),

			'%b %d %Y %H:%M' 	=> strftime('%b %d %Y %H:%M', $time),
			'%B %d %Y %H:%M' 	=> strftime('%B %d %Y %H:%M', $time),

			'%b %d %Y %I:%M %p' => strftime('%b %d %Y %I:%M %p', $time),
			'%B %d %Y %I:%M %p' => strftime('%B %d %Y %I:%M %p', $time),
		);
	}

	return tpl_options($dateFormats, $selected);
}


/**
 * Options for a <select>
 *
 * FIXME peterdd: This function is currently often called by templates with just
 * results from sqltablequeries like  select * from tablex,
 * so data[0] and data[1] of each row works only by table structure convention as wished.
 * not by names, lack of a generic optgroup feature, css-id, css-classes, disabled option.
 * Maybe rewrite a as tpl_select() ..
 *
 * @options array of values
 * For optgroups, the values should be presorted by the optgroups
 * example:
 * $options=array(
 * 	array(3,'project3',1), # active project group
 * 	array(2,'project2',1),
 * 	array(5,'project5',0)  # inactive project optgroup
 * ); tpl_options($options, 2)
*/
function tpl_options($options, $selected = null, $labelIsValue = false, $attr = null, $remove = null)
{
	$html = '';

	// force $selected to be an array.
	// this allows multi-selects to have multiple selected options.
	$selected = is_array($selected) ? $selected : (array) $selected;
	$options = is_array($options) ? $options : (array) $options;

	$lastoptgroup=0;
	$optgroup=0;
        $ingroup=false;
	foreach ($options as $idx=>$data) {
		if (is_array($data)) {
			$value = $data[0];
			$label = $data[1];
			# just a temp hack, we currently use optgroups only for project dropdown...
			$optgroup=array_key_exists('project_is_active',$data) ? $data['project_is_active'] : 0;
			if (array_key_exists('project_title', $data) && $optgroup!=$lastoptgroup) {
				if ($ingroup) {
					$html.='</optgroup>';
				}
				$html.='<optgroup'.($optgroup==0 ? ' label="'.L('inactive').'"' : '' ).'>';
				$ingroup=true;
			}
		} else{
			$value=$idx;
			$label=$data;
		}
		$label = htmlspecialchars($label, ENT_QUOTES, 'utf-8');
		$value = $labelIsValue ? $label : htmlspecialchars($value, ENT_QUOTES, 'utf-8');

		if ($value === $remove) {
			continue;
		}

		$html .= '<option value="'.$value.'"';
		if (in_array($value, $selected)) {
			$html .= ' selected="selected"';
		}
		$html .= ($attr ? join_attrs($attr): '') . '>' . $label . '</option>';
		$lastoptgroup=$optgroup;
	}

	if ($ingroup) {
	$html.='</optgroup>';
	}

	if (!$html) {
		$html .= '<option value="0">---</option>';
	}

	return $html;
}


/**
 * Builds a complete HTML-select with select options.
 *
 * Supports free choosable attributes for select, options and optgroup tags. optgroups can also be nested.
 *
 * @author peterdd
 *
 * @param array key-values pairs and can be nested
 *
 * @return string the complete html-select
 *
 * @since 1.0.0-beta3
 *
 * @example
 * example output of print_r($array) to see the structure of the param $array
 * Array
  (
    [name] => varname          // required if you want submit it with a form to the server
    [attr] => Array            // optional
        (
            [id] => selid   // optional
            [class] => selclass1  // optional
        )
    [options] => Array         // optional, but without doesn't make much sense ;-)
        (
            [0] => Array       // at least one would be useful
                (
                    [value] => opt1val     // recommended
                    [label] => opt1label   // recommended
                    [disabled] => 1        // optional
                    [selected] => 1        // optional
                    [attr] => Array        // optional
                        (
                            [id] => opt1id        // optional
                            [class] => optclass1  // optional
                        )
                )
            [1] => Array
                (
                    [optgroup] => 1          // this tells the function that now comes an optgroup
                    [label] => optgrouplabel // optional
                    [attr] => Array          // optional
                        (
                            [id] => optgroupid1        // optional
                            [class] => optgroupclass1  // optional
                        )
                    [options] => Array
                        // ... nested options and optgroups can follow here....
                )
                // ... and so on
        )
  )
 */
function tpl_select($select=array()){

	if(isset($select['name'])){
		$name=' name="'.$select['name'].'"';
	}else{
		$name='';
	}

	$attrjoin='';
	if(isset($select['attr'])){
		foreach($select['attr'] as $key=>$val){
			$attrjoin.=' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES, 'utf-8').'"';
		}
	}
	$html='<select'.$name.$attrjoin.'>';
	if(isset($select['options'])){
		$html.=tpl_selectoptions($select['options']);
	}
	$html.="\n".'</select>';
	return $html;
}

/**
 * called by tpl_select()
 *
 * @author peterdd
 *
 * @param array key-values pairs and can be nested
 *
 * @return string option- and optgroup-tags as one string
 *
 * @since 1.0.0-beta3
 *
 * called recursively by itself
 * Can also be called alone from template if the templates writes the wrapping select-tags.
 *
 * @example see [options]-array of example of tpl_select()
 */
function tpl_selectoptions($options=array(), $level=0){
	$html='';
	# such deep nesting is too weired - probably an endless loop lets
	# return before something bad happens
	if( $level>10){
		return;
	}
	#print_r($options);
	#print_r($level);
	foreach($options as $o){
		if(isset($o['optgroup'])){
			# we have an optgroup
			$html.="\n".str_repeat("\t",$level).'<optgroup label="'.$o['label'].'"';
			if(isset($o['attr'])){
				foreach($o['attr'] as $key=>$val){
					$html.=' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES, 'utf-8').'"';
				}
			}
			$html.='>';
			# may contain options and suboptgroups..
			$html.=tpl_selectoptions($o['options'], $level+1);
			$html.="\n".str_repeat("\t",$level).'</optgroup>';
		} else{
			# we have a simple option
			$html.="\n".str_repeat("\t",$level).'<option value="'.htmlspecialchars($o['value'], ENT_QUOTES, 'utf-8').'"';
			if(isset($o['disabled'])){
				$html.=' disabled="disabled"'; # xhtml compatible
			}
			if(isset($o['selected'])){
				$html.=' selected="selected"'; # xhtml compatible
			}
			if(isset($o['attr'])){
				foreach($o['attr'] as $key=>$val){
					$html.=' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES, 'utf-8').'"';
				}
			}
			$html.='>'.htmlspecialchars($o['label'], ENT_QUOTES, 'utf-8').'</option>';
		}
	}

	return $html;
}


/**
 * Creates a double select.
 *
 * Elements of arrays $options and $selected can be moved between eachother. The $selected list can also be sorted.
 *
 * @param string name
 * @param array options
 * @param array selected
 * @param bool labelisvalue
 * @param bool updown
 */
function tpl_double_select($name, $options, $selected = null, $labelisvalue = false, $updown = true)
{
    static $_id = 0;
    static $tpl = null;

    if (!$tpl) {
    	global $proj;

        // poor man's cache
        $tpl = new FSTpl();
        $tpl->setTheme($proj->prefs['theme_style']);
    }

    settype($selected, 'array');
    settype($options, 'array');

    $tpl->assign('id', '_task_id_'.($_id++));
    $tpl->assign('name', $name);
    $tpl->assign('selected', $selected);
    $tpl->assign('updown', $updown);

    $html = $tpl->fetch('common.dualselect.tpl');

    $selectedones = array();

    $opt1 = '';
    foreach ($options as $value => $label) {
        if (is_array($label) && count($label) >= 2) {
            $value = $label[0];
            $label = $label[1];
        }
        if ($labelisvalue) {
            $value = $label;
        }
        if (in_array($value, $selected)) {
            $selectedones[$value] = $label;
            continue;
        }
        $label = htmlspecialchars($label, ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $opt1 .= sprintf('<option title="%2$s" value="%1$s">%2$s</option>', $value, $label);
    }

    $opt2 = '';
    foreach ($selected as $value) {
        if (!isset($selectedones[$value])) {
            continue;
        }
        $label = htmlspecialchars($selectedones[$value], ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $opt2 .= sprintf('<option title="%2$s" value="%1$s">%2$s</option>', $value, $label);
    }

    return sprintf($html, $opt1, $opt2);
}

/**
 * Creates a HTML checkbox
 *
 * @param string name
 * @param bool checked
 * @param string id id attribute of the checkbox HTML element
 * @param string value
 * @param array attr tag attributes
 *
 * @return string for ready for HTML output
 */
function tpl_checkbox($name, $checked = false, $id = null, $value = 1, $attr = null)
{
	$name  = htmlspecialchars($name,  ENT_QUOTES, 'utf-8');
	$value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');
	$html  = sprintf('<input type="checkbox" name="%s" value="%s" ', $name, $value);
	if (is_string($id)) {
		$html .= sprintf('id="%s" ', Filters::noXSS($id));
	}
	if ($checked == true) {
		$html .= 'checked="checked" ';
	}
	// do not call join_attrs if $attr is null or nothing..
	return ($attr ? $html. join_attrs($attr) : $html) . '/>';
}

/**
 * Image display
 */
function tpl_img($src, $alt = '')
{
    global $baseurl;
    if (is_file(BASEDIR .'/'.$src)) {
        return sprintf('<img src="%s%s" alt="%s" />', $baseurl, Filters::noXSS($src), Filters::noXSS($alt));
    }
    return Filters::noXSS($alt);
}

// Text formatting
//format has been already checked in constants.inc.php
if(isset($conf['general']['syntax_plugin'])) {

    $path_to_plugin = BASEDIR . '/plugins/' . $conf['general']['syntax_plugin'] . '/' . $conf['general']['syntax_plugin'] . '_formattext.inc.php';

    if (is_readable($path_to_plugin)) {
        include $path_to_plugin;
    }
}

class TextFormatter
{
    public static function get_javascript()
    {
        global $conf;

        $path_to_plugin = sprintf('%s/plugins/%s', BASEDIR, $conf['general']['syntax_plugin']);
         $return = array();

        if (!is_readable($path_to_plugin)) {
            return $return;
        }

        $d = dir($path_to_plugin);
        while (false !== ($entry = $d->read())) {
           if (substr($entry, -3) == '.js') {
                $return[] = $conf['general']['syntax_plugin'] . '/' . $entry;
            }
        }

        return $return;
    }

	public static function render($text, $type = null, $id = null, $instructions = null)
	{
		global $conf;

		$methods=array();
		if(class_exists($conf['general']['syntax_plugin'] . '_TextFormatter')){
			$methods = get_class_methods($conf['general']['syntax_plugin'] . '_TextFormatter');
		}
		$methods = is_array($methods) ? $methods : array();

		if (in_array('render', $methods)) {
			return call_user_func(array($conf['general']['syntax_plugin'] . '_TextFormatter', 'render'),
				$text, $type, $id, $instructions);
		} else {
			$text=strip_tags($text, '<br><br/><p><h2><h3><h4><h5><h5><h6><blockquote><a><img><u><b><strong><em><s><ins><del><ul><ol><li><table><caption><tr><col><colgroup><td><th><thead><tfoot><tbody><pre><code><hr>');
			if (   $conf['general']['syntax_plugin']
				&& $conf['general']['syntax_plugin'] != 'none'
				&& $conf['general']['syntax_plugin'] != 'html') {
				$text='Unsupported output plugin '.$conf['general']['syntax_plugin'].'!'
					.'<br/>Couldn\'t call '.$conf['general']['syntax_plugin'].'_TextFormatter::render()'
					.'<br/>Temporarily handled like it is HTML until fixed.<br/>'
					.$text;
			}

			if ($conf['general']['syntax_plugin'] != 'html') {
				$text = nl2br($text);
			}

			// Change FS#123 into hyperlinks to tasks
			return preg_replace_callback("/\b(?:FS#|bug )(\d+)\b/", 'tpl_fast_tasklink', trim($text));
		}
	}

	public static function textarea($name, $rows, $cols, $attrs = null, $content = null)
	{
		global $conf;

		if (class_exists($conf['general']['syntax_plugin'] . '_TextFormatter')
		    && in_array('textarea', get_class_methods($conf['general']['syntax_plugin'] . '_TextFormatter'))) {
			return call_user_func(array($conf['general']['syntax_plugin'] . '_TextFormatter', 'textarea'),
			                      $name, $rows, $cols, $attrs, $content);
		}

		$name = htmlspecialchars($name, ENT_QUOTES, 'utf-8');
		$return = sprintf('<textarea name="%s" cols="%d" rows="%d"', $name, $cols, $rows);
		if (is_array($attrs) && count($attrs)) {
			$return .= join_attrs($attrs);
		}
		$return .= '>';
		if (is_string($content) && strlen($content)) {
			$return .= htmlspecialchars($content, ENT_QUOTES, 'utf-8');
		}
		$return .= '</textarea>';

		# Activate CkEditor on textareas
		if($conf['general']['syntax_plugin']=='html'){
			$return .= "
<script>
	CKEDITOR.replace( '".$name."', { entities: true, entities_latin: false, entities_processNumerical: false } );
</script>";
		}

		return $return;
	}
}

/**
 * Format Date
 *
 * Questionable if this function belongs in this class. Usages also elsewhere and not UI-related.
 */
function formatDate($timestamp, $extended = false, $default = '')
{
    global $db, $conf, $user, $fs;

    setlocale(LC_ALL, str_replace('-', '_', L('locale')) . '.utf8');

    if (!$timestamp) {
        return $default;
    }

    $dateformat = '';
    $format_id  = $extended ? 'dateformat_extended' : 'dateformat';
    $st = date('Z')/3600; // server GMT timezone

    if (!$user->isAnon()) {
        $dateformat = $user->infos[$format_id];
        $timestamp += ($user->infos['time_zone'] - $st) * 60 * 60;
        $st = $user->infos['time_zone'];
    }

    if (!$dateformat) {
        $dateformat = $fs->prefs[$format_id];
    }

    if (!$dateformat) {
        $dateformat = $extended ? '%A, %d %B %Y, %H:%M %GMT' : '%Y-%m-%d';
    }

    $zone = L('GMT') . (($st == 0) ? ' ' : (($st > 0) ? '+' . $st : $st));
    $dateformat = str_replace('%GMT', $zone, $dateformat);
    //it returned utf-8 encoded by the system
    return strftime(Filters::noXSS($dateformat), (int) $timestamp);
}

/**
 * Draw permissions table
 */
function tpl_draw_perms($perms)
{
	global $proj;

	$perm_fields = array(
		'is_admin',
		'manage_project',
		'view_tasks',
		'view_groups_tasks',
		'view_own_tasks',
		'open_new_tasks',
		'add_multiple_tasks',
		'modify_own_tasks',
		'modify_all_tasks',
		'create_attachments',
		'delete_attachments',
		'assign_to_self',
		'assign_others_to_self',
		'edit_assignments',
		'close_own_tasks',
		'close_other_tasks',
		'view_roadmap',
		'view_history',
		'view_reports',
		'add_votes',
		'view_comments',
		'add_comments',
		'edit_comments',
		'edit_own_comments',
		'delete_comments',
		'view_estimated_effort',
		'view_current_effort_done',
		'track_effort'
	);

	$yesno = array(
		'<td class="bad fa fa-ban" title="'.eL('no').'"></td>',
		'<td class="good fa fa-check" title="'.eL('yes').'"></td>'
	);

    # 20150307 peterdd: This a temporary hack
    $i=0;
    $html='';
    $projpermnames='';

    foreach ($perms as $projperm){
        $html .= '<table class="perms"><thead><tr><th>'.($i==0? 'global' : L('project').' '.$i).'</th>'.($i==0? '<th>'.L('permissions').'</th>' : '').'</tr></thead><tbody>';
        foreach ($projperm as $key => $val) {
            if (!is_numeric($key) && in_array($key, $perm_fields)) {
               $html .= '<tr>';
               $html .= $yesno[ ($val || $perms[0]['is_admin']) ];
               $html .= $i==0 ? '<th>'.eL(str_replace('_','',$key)).'</th>' : '';
               $html .= '</tr>';

               # all projects have same permnames
               $projpermnames .= $i==1 ? '<tr><td>'.eL(str_replace('_','',$key)).'</td></tr>' : '';
            }
        }
        $html.= '</tbody></table>';
        $i++;
    }
    $html.='<table class="perms"><thead><th>'.L('permissions').'</th></thead><tbody>'.$projpermnames.'</tbody></table>';
    $html.='<style>.perms tr{height:30px;}</style>';
    # end 20150307
    return $html;
}

/**
 * Highlights searchqueries in HTML code
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight($html,$query){
  //split at common delimiters
  $queries = preg_split ('/[\s\'"\\\\`()\]\[?:!\.{};,#+*<>]+/',$query,-1,PREG_SPLIT_NO_EMPTY);
  foreach ($queries as $q){
     $q = preg_quote($q,'/');
     $html = preg_replace_callback("/((<[^>]*)|$q)/i",'html_hilight_callback',$html);
  }
  return $html;
}

/**
 * Callback used by html_hilight()
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight_callback($m) {
  $hlight = unslash($m[0]);
  if ( !isset($m[2])) {
    $hlight = '<span class="search_hit">'.$hlight.'</span>';
  }
  return $hlight;
}

/**
 * XHTML compatible output of disabled attribute
 *
 * @param bool if something that PHP sees as true or false
 */
function tpl_disableif ($if)
{
    if ($if) {
        return 'disabled="disabled"';
    }
}

/**
 * Generates links for Flyspray
 *
 * Create an URL based upon address-rewriting preferences
 *
 */
function createURL($type, $arg1 = null, $arg2 = null, $arg3 = array())
{
	global $baseurl, $conf, $fs;

	$url = $baseurl;

	// If we do want address rewriting
	if ($fs->prefs['url_rewriting']) {
		switch ($type) {
			case 'depends':
				$return = $url . 'task/' . $arg1 . '/' . $type;
				break;

			case 'details':
				$return = $url . 'task/' . $arg1;
				break;

			case 'edittask':
				$return = $url . 'task/' . $arg1 . '/edit';
				break;

			case 'pm':
				$return = $url . 'pm/proj' . $arg2 . '/' . $arg1;
				break;

			case 'admin':
			case 'edituser':
			case 'user':
				$return = $url . $type . '/' . $arg1;
				break;

			case 'project':
				$return = $url . 'proj' . $arg1;
                break;

			case 'reports':
			case 'roadmap':
			case 'toplevel':
			case 'gantt':
			case 'kanban':
			case 'index':
				$return = $url.$type.'/proj'.$arg1;
				break;

			case 'newtask':
			case 'newmultitasks':
				$return = $url . $type . '/proj' . $arg1 . ($arg2 ? '/supertask' . $arg2 : '');
				break;

			case 'editgroup':
				$return = $url . $arg2 . '/' . $type . '/' . $arg1;
				break;

			case 'logout':
			case 'lostpw':
			case 'myprofile':
			case 'register':
				$return = $url . $type;
				break;

			case 'mytasks':
				$return = $url.'proj'.$arg1.'/dev'.$arg2;
				break;

            case 'tasklist':
				# see also .htaccess for the mapping
				if ($arg1>0 && $fs->projects[$arg1]['default_entry'] == 'index'){
					$return = $url.'proj'.$arg1;
				} else {
					$return = $url.$type.'/proj'.$arg1;
				}
            	break;
			default:
				$return = $baseurl . 'index.php';
				break;
		}
	} else {
		if ($type == 'edittask') {
			$url .= 'index.php?do=details';
		} else {
			$url .= 'index.php?do=' . $type;
		}

		switch ($type) {
            case 'admin':
                $return = $url . '&area=' . $arg1;
                break;
            case 'edittask':
                $return = $url . '&task_id=' . $arg1 . '&edit=yep';
                break;
            case 'pm':
                $return = $url . '&area=' . $arg1 . '&project=' . $arg2;
                break;
            case 'user':
                $return = $baseurl . 'index.php?do=user&area=users&id=' . $arg1;
                break;
            case 'edituser':
                $return = $baseurl . 'index.php?do=admin&area=users&user_id=' . $arg1;
                break;
            case 'logout':
                $return = $baseurl . 'index.php?do=authenticate&logout=1';
                break;

            case 'details':
            case 'depends':
                $return = $url . '&task_id=' . $arg1;
                break;

            case 'project':
                $return = $baseurl . 'index.php?project=' . $arg1;
                break;

            case 'reports':
            case 'roadmap':
            case 'toplevel':
            case 'gantt':
            case 'kanban':
            case 'index':
            case 'tasklist':
            	$return = $url . '&project=' . $arg1;
            	break;

            case 'newtask':
            case 'newmultitasks':
                $return = $url . '&project=' . $arg1 . ($arg2 ? '&supertask=' . $arg2 : '');
                break;

            case 'editgroup':
                $return = $baseurl . 'index.php?do=' . $arg2 . '&area=editgroup&id=' . $arg1;
                break;

            case 'lostpw':
            case 'myprofile':
            case 'register':
            	$return = $url;
            	break;

            case 'mytasks':
            	$return = $baseurl.'index.php?do=index&project='.$arg1.'&dev='.$arg2;
            	break;

            default:
        		$return = $baseurl . 'index.php';
        		break;
		}
	}

	$url = new Url($return);
	if (!is_null($arg3) && count($arg3)) {
		$url->addvars($arg3);
	}
	return $url->get();
}

/**
 * tasklist pagination
 *
 * Generates the HTML for pagination navigation of tasklist.
 * Uses the global $proj and $_GET parameters to generate links with the current search filter.
 *
 * @param int $pagenum
 * @param int $perpage
 * @param int $totalcount
 * @param string $pagetype optional
 * @param string $pagearea optional
 *
 * @return string
 */
function pagenums($pagenum, $perpage, $totalcount, $pagetype='tasklist', $pagearea=null)
{
	$pagenum = intval($pagenum);
	$perpage = intval($perpage);
	$totalcount = intval($totalcount);

	if($pagetype=='tasklist'){
		$pagearea = $GLOBALS['proj']->id;
	}

	// Just in case $perpage is something weird, like 0, fix it here:
	if ($perpage < 1) {
		$perpage = $totalcount > 0 ? $totalcount : 1;
	}
	$pages  = ceil($totalcount / $perpage);
	$output = '';
	$output .= '<span class="pagerange">'.sprintf(eL('page'), $pagenum, $pages).'</span>';

	if ( $totalcount / $perpage > 1 ) {
		$params=$_GET;
		# unset some to avoid unneeded or duplicated parameters for createURL()
		unset($params['do']); # 1th paramater of createURL()
		unset($params['project']); # 2th parameter of createURL()
		unset($params['switch']); # not needed
		unset($params['area']); # duplicated admin area 
		$output .= '<nav class="pagenums" aria-label="Search results pages">';

		$neighborsprev=5;
		$neighborsnext=5;

		$start  = max(2, $pagenum - 1 - $neighborsprev + min(1, $pages - $pagenum));
		$finish = min($start + $neighborsprev + $neighborsnext, $pages);

		$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => 1))));
		$firstactive = ($pagenum==1) ? 'first active' : 'first';
		$output .= sprintf('<a class="%s" href="%s" aria-label="%s">%s</a>', $firstactive, $url, eL('first'), 1);
		
		if ($pagenum > 1) {
			#$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => $pagenum - 1))));
			#$output .= sprintf('<a class="previous" accesskey="p" href="%s" aria-label="%s">%s</a>', $url, eL('previous'), eL('previous'));
		}

		if ($pagenum > 4 && $pagenum < 9){
			$output.='<span class="distancefargap"></span>';
		}
			
		if ($start==3){
			$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => 2))));
			$distclass= (abs($pagenum - 2) > 2) ? ' class="distancefar"' : '';
			$output .= sprintf('<a href="%s"%s>%d</a>', $url, $distclass, 2);
		} elseif ($start>3) {
			$output .= '<span class="ellipsis"></span>';
		}

		for ($pagelink = $start; $pagelink <= $finish;  $pagelink++) {
			if ($pagelink == $pagenum) {
				$output .= sprintf('<span class="active">%d</span>', $pagelink);
			} else {
				$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => $pagelink))));
				# Enables CSS themes to shorten the pagination bar by hiding some links on small screens by media queries.
				$distclass= ($pagelink != 1 && abs($pagenum - $pagelink) > 2) ? ' class="distancefar"' : '';
				$output .= sprintf('<a href="%s"%s>%d</a>', $url, $distclass, $pagelink);
			}
		}

		if ($pagenum < $pages) {
			#$url =  Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => $pagenum + 1))));
			#$output .= sprintf('<a class="next" accesskey="n" href="%s" aria-label="%s">%s</a>', $url, eL('next'), eL('next'));

			if($finish == ($pages-2)){
				$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => $pages - 1 ))));
				$output .= sprintf('<a href="%s">%d</a>', $url, $pages-1);
			} else if ($finish < ($pages-2)) {
				$output .= '<span class="ellipsis"></span>';
			}
		}
		if ($finish < $pages) {
			$url = Filters::noXSS(createURL($pagetype, $pagearea, null, array_merge($params, array('pagenum' => $pages))));
			#$output .= sprintf('<a class="last" href="%s" aria-label="%s">%s</a>', $url, eL('last'), eL('last'));
			$output .= sprintf('<a class="last" href="%s" aria-label="%s">%s</a>', $url, eL('last'), $pages);
		}
		$output .= '</nav>';
	}

	return $output;
}

class Url {
    public $url = '';
    public $parsed;

    public function __construct($url = '') {
        $this->url = $url;
        $this->parsed = parse_url($this->url);
    }

    public function seturl($url) {
        $this->url = $url;
        $this->parsed = parse_url($this->url);
    }

    public function getinfo($type = null) {
        if (is_null($type)) {
            return $this->parsed;
        } elseif (isset($this->parsed[$type])) {
            return $this->parsed[$type];
        } else {
            return '';
        }
    }

    public function setinfo($type, $value) {
        $this->parsed[$type] = $value;
    }

    public function addfrom($method = 'get', $vars = array()) {
        $append = '';
        foreach($vars as $key) {
            $append .= http_build_query( (($method == 'get') ? Get::val($key) : Post::val($key)) ) . '&';
        }
        $append = substr($append, 0, -1);

        $separator = ini_get('arg_separator.output');
        if (strlen($separator) != 0) {
            $append = str_replace($separator, '&', $append);
        }

        if ($this->getinfo('query')) {
            $this->parsed['query'] .= '&' . $append;
        } else {
            $this->parsed['query'] = $append;
        }
    }

    public function addvars($vars = array()) {
        $append = http_build_query($vars);

        $separator = ini_get('arg_separator.output');
        if (strlen($separator) != 0) {
            $append = str_replace($separator, '&', $append);
        }

        if ($this->getinfo('query')) {
            $this->parsed['query'] .= '&' . $append;
        } else {
            $this->parsed['query'] = $append;
        }
    }

    public function get($fullpath = true) {
        $return = '';
        if ($fullpath) {
            $return .= $this->getinfo('scheme') . '://' . $this->getinfo('host');

            if ($this->getinfo('port')) {
                $return .= ':' . $this->getinfo('port');
            }
        }

        $return .= $this->getinfo('path');

        if ($this->getinfo('query')) {
            $return .= '?' . $this->getinfo('query');
        }

        if ($this->getinfo('fragment')) {
            $return .= '#' . $this->getinfo('fragment');
        }

        return $return;
    }
}
