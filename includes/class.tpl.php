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

    // {{{ Display page
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

        // theming part
        // FIXME: Shouldn't have to do this but there is a bug somewhere cause theme to sometimes come in as empty
        if (strlen($this->_theme) == 0) {
            $this->_theme = 'CleanFS/';
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
        } else {
            // This is needed to catch times when there is no theme (for example setup pages)
            require BASEDIR . "/templates/" . $_tpl;
        }

    } // }}}

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

// {{{ costful templating functions, TODO: optimize them

function tpl_tasklink($task, $text = null, $strict = false, $attrs = array(), $title = array('status','summary','percent_complete'))
{
    global $user;

    $params = array();

    if (!is_array($task) || !isset($task['status_name'])) {
        $td_id = (is_array($task) && isset($task['task_id'])) ? $task['task_id'] : $task;
        $task = Flyspray::GetTaskDetails($td_id, true);
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
                        $task = Flyspray::GetTaskDetails($task['task_id'], true);
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

    $url = htmlspecialchars(CreateURL('details', $task['task_id'],  null, $params), ENT_QUOTES, 'utf-8');
    $title_text = htmlspecialchars($title_text, ENT_QUOTES, 'utf-8');
    $link  = sprintf('<a href="%s" title="%s" %s>%s</a>',$url, $title_text, join_attrs($attrs), $text);

    if ($task['is_closed']) {
        $link = '<del>&#160;' . $link . '&#160;</del>';
    }
    return $link;
}

function tpl_userlink($uid)
{
    global $db, $user;

    static $cache = array();

    if (is_array($uid)) {
        list($uid, $uname, $rname) = $uid;
    } elseif (empty($cache[$uid])) {
        $sql = $db->Query('SELECT user_name, real_name FROM {users} WHERE user_id = ?',
                           array(intval($uid)));
        if ($sql && $db->countRows($sql)) {
            list($uname, $rname) = $db->fetchRow($sql);
        }
    }

    if (isset($uname)) {
        $url = CreateURL(($user->perms('is_admin')) ? 'edituser' : 'user', $uid);
        $cache[$uid] = vsprintf('<a href="%s">%s</a>', array_map(array('Filters', 'noXSS'), array($url, $rname)));
    } elseif (empty($cache[$uid])) {
        $cache[$uid] = eL('anonymous');
    }

    return $cache[$uid];
}

function tpl_userlinkgravatar($uid, $size, $float = 'left', $padding = '0px')
{
	global $db, $user;
	if (is_array($uid)) {
		list($uid, $uname, $rname) = $uid;
	}

	$sql = $db->Query('SELECT user_name, real_name, email_address FROM {users} WHERE user_id = ?',
					array(intval($uid)));
	if ($sql && $db->countRows($sql)) {
		list($uname, $rname, $email) = $db->fetchRow($sql);
	}
	else {
		return;
	}

	$email = md5(strtolower(trim($email)));
	$default = 'mm';

	$sql = $db->Query('SELECT profile_image FROM {users} WHERE user_id = ?', array(intval($uid)));
	if ($sql && $db->countRows($sql))
	{
		$avatar_name = $db->fetchRow($sql);
		if (is_file(BASEDIR.'/avatars/'.$avatar_name['profile_image'])) {
			$image = "<img src='./avatars/".$avatar_name['profile_image']."' alt='".$avatar_name['profile_image']."' width='".$size."' height='".$size."'/>";
		}
		else
		{
			$url = 'http://www.gravatar.com/avatar/'.$email.'?d='.urlencode($default).'&s='.$size;
			$image = '<img src='.$url.'/>';
		}
	}
	else {
		$image = '';
	}

	if (isset($uname)) {
		$url = CreateURL(($user->perms('is_admin')) ? 'edituser' : 'user', $uid);
		//$link = vsprintf('<a href="%s">%s</a>', array_map(array('Filters', ''), array($url, $image)));
		$link = "<a style='float: ".$float."; padding: ".$padding."' href=".$url." title='".$rname."'>".$image."</a>";
	}
	return $link;
}


function tpl_fast_tasklink($arr)
{
    return tpl_tasklink($arr[1], $arr[0]);
}

// }}}
// {{{ some useful plugins

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
// {{{ Datepicker
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
// }}}
// {{{ user selector
function tpl_userselect($name, $value = null, $id = '', $attrs = array()) {
    global $db, $user;

    if (!$id) {
        $id = $name;
    }

    if ($value && ctype_digit($value)) {
        $sql = $db->Query('SELECT user_name FROM {users} WHERE user_id = ?', array($value));
        $value = $db->FetchOne($sql);
    }

    if (!$value) {
        $value = '';
    }


    $page = new FSTpl;
    $page->assign('name', $name);
    $page->assign('id', $id);
    $page->assign('value', $value);
    $page->assign('attrs', $attrs);
    $page->display('common.userselect.tpl');
}
// }}}

// {{{ Options for a <select>
function tpl_options($options, $selected = null, $labelIsValue = false, $attr = null, $remove = null)
{
    $html = '';

    // force $selected to be an array.
    // this allows multi-selects to have multiple selected options.

    // operate by value ..
    $selected = is_array($selected) ? $selected : (array) $selected;
    $options = is_array($options) ? $options : (array) $options;

    foreach ($options as $value=>$label)
    {
        if (is_array($label)) {
            $value = $label[0];
            $label = $label[1];
        }
        $label = htmlspecialchars($label, ENT_QUOTES, 'utf-8');
        $value = $labelIsValue ? $label
                               : htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        if ($value === $remove) {
            continue;
        }

        $html .= '<option value="'.$value.'"';
        if (in_array($value, $selected)) {
            $html .= ' selected="selected"';
        }
        $html .= ($attr ? join_attrs($attr): '') . '>' . $label . '</option>';
    }
    if (!$html) {
        $html .= '<option value="0">---</option>';
    }

    return $html;
} // }}}
// {{{ Double <select>
function tpl_double_select($name, $options, $selected = null, $labelIsValue = false, $updown = true)
{
    static $_id = 0;
    static $tpl = null;

    if (!$tpl) {
        // poor man's cache
        $tpl = new FSTpl();
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
        if ($labelIsValue) {
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
} // }}}
// {{{ Checkboxes
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
} // }}}
// {{{ Image display
function tpl_img($src, $alt = '')
{
    global $baseurl;
    if (is_file(BASEDIR .'/'.$src)) {
        return sprintf('<img src="%s%s" alt="%s" />', $baseurl, Filters::noXSS($src), Filters::noXSS($alt));
    }
    return Filters::noXSS($alt);
} // }}}
// {{{ Text formatting
//format has been already checked in constants.inc.php
if(isset($conf['general']['syntax_plugin'])) {

    $path_to_plugin = BASEDIR . '/plugins/' . $conf['general']['syntax_plugin'] . '/' . $conf['general']['syntax_plugin'] . '_formattext.inc.php';

    if (is_readable($path_to_plugin)) {
        include($path_to_plugin);
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

        $methods = get_class_methods($conf['general']['syntax_plugin'] . '_TextFormatter');
        $methods = is_array($methods) ? $methods : array();

        if (in_array('render', $methods)) {
            return call_user_func(array($conf['general']['syntax_plugin'] . '_TextFormatter', 'render'),
                                  $text, $type, $id, $instructions);
        } else {
            //TODO: Remove Redundant Code once tested completely
            //Author: Steve Tredinnick
            //Have removed this as creating additional </br> lines even though <p> is already dealing with it
            //possibly an conversion from Dokuwiki syntax to html issue, left in in case anyone has issues and needs to comment out
            //$text = ' ' . nl2br($text) . ' ';

            // Change FS#123 into hyperlinks to tasks
            return preg_replace_callback("/\b(?:FS#|bug )(\d+)\b/", 'tpl_fast_tasklink', trim($text));
        }
    }

    public static function textarea($name, $rows, $cols, $attrs = null, $content = null)
    {
        global $conf;

        if (@in_array('textarea', get_class_methods($conf['general']['syntax_plugin'] . '_TextFormatter'))) {
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

        //Activate CkEditor on TextAreas.
        $return .= "<script>
                        CKEDITOR.replace( '".$name."' );
                    </script>";
        return $return;
    }
}
// }}}
// Format Date {{{
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
} /// }}}
// {{{ Draw permissi ons table
function tpl_draw_perms($perms)
{
    global $proj;

    $perm_fields = array('is_admin', 'manage_project', 'view_tasks',
            'open_new_tasks', 'modify_own_tasks', 'modify_all_tasks', 'edit_assignments',
            'view_comments', 'add_comments', 'edit_comments', 'delete_comments',
            'create_attachments', 'delete_attachments',
            'view_history', 'close_own_tasks', 'close_other_tasks',
            'assign_to_self', 'assign_others_to_self', 'view_reports',
            'add_votes', 'edit_own_comments','view_effort','track_effort');

    $yesno = array(
            '<td class="bad">' . eL('no') . '</td>',
            '<td class="good">' . eL('yes') . '</td>');

    // FIXME: html belongs in a template, not in the template class
    $html = '<table border="1" onmouseover="perms.hide()" onmouseout="perms.hide()">';
    $html .= '<thead><tr><th colspan="2">';
    $html .= htmlspecialchars(L('permissionsforproject').$proj->prefs['project_title'], ENT_QUOTES, 'utf-8');
    $html .= '</th></tr></thead><tbody>';

    foreach ($perms[$proj->id] as $key => $val) {
        if (!is_numeric($key) && in_array($key, $perm_fields)) {
            $html .= '<tr><th>' . eL(str_replace('_', '', $key)) . '</th>';
            $html .= $yesno[ ($val || $perms[0]['is_admin']) ].'</tr>';
        }
    }
    return $html . '</tbody></table>';
} // }}}

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

function tpl_disableif ($if)
{
    if ($if) {
        return 'disabled="disabled"';
    }
}

// {{{ Url handling
// Create an URL bas ed upon address-rewriting preferences {{{
function CreateURL($type, $arg1 = null, $arg2 = null, $arg3 = array())
{
    global $baseurl, $conf;

    $url = $baseurl;

    // If we do want address rewriting
    if ($conf['general']['address_rewriting'] == '1') {
        switch ($type) {
            case 'depends':   $return = $url . 'task/' .  $arg1 . '/' . $type; break;
            case 'details':   $return = $url . 'task/' . $arg1; break;
            case 'edittask':  $return = $url . 'task/' .  $arg1 . '/edit'; break;
            case 'pm':        $return = $url . 'pm/proj' . $arg2 . '/' . $arg1; break;

            case 'admin':
            case 'edituser':
            case 'user':      $return = $url . $type . '/' . $arg1; break;

            case 'project':   $return = $url . 'proj' . $arg1; break;

            case 'toplevel':
            case 'roadmap':
            case 'index':
	    case 'newtask':
	    case 'newmultitasks':   $return = $url . $type .  '/proj' . $arg1 . ($arg2 ? '/supertask' . $arg2 : ''); break;

            case 'editgroup': $return = $url . $arg2 . '/' . $type . '/' . $arg1; break;

            case 'logout':
            case 'lostpw':
            case 'myprofile':
            case 'register':
            case 'reports':  $return = $url . $type; break;
        }
    } else {
        if ($type == 'edittask') {
            $url .= 'index.php?do=details';
        } else {
            $url .= 'index.php?do=' . $type;
        }

        switch ($type) {
            case 'admin':     $return = $url . '&area=' . $arg1; break;
            case 'edittask':  $return = $url . '&task_id=' . $arg1 . '&edit=yep'; break;
            case 'pm':        $return = $url . '&area=' . $arg1 . '&project=' . $arg2; break;
            case 'user':      $return = $baseurl . 'index.php?do=user&area=users&id=' . $arg1; break;
            case 'edituser':  $return = $baseurl . 'index.php?do=admin&area=users&user_id=' . $arg1; break;
            case 'logout':    $return = $baseurl . 'index.php?do=authenticate&logout=1'; break;

            case 'details':
            case 'depends':   $return = $url . '&task_id=' . $arg1; break;

            case 'project':   $return = $baseurl . 'index.php?project=' . $arg1; break;

            case 'roadmap':
            case 'toplevel':
            case 'index':
	    case 'newtask':
	    case 'newmultitasks': $return = $url . '&project=' . $arg1 . ($arg2 ? '&supertask=' . $arg2 : ''); break;

            case 'editgroup': $return = $baseurl . 'index.php?do=' . $arg2 . '&area=editgroup&id=' . $arg1; break;

            case 'lostpw':
            case 'myprofile':
            case 'register':
            case 'reports':   $return = $url; break;
        }
    }

    $url = new Url($return);
    if (count($arg3)) {
        $url->addvars($arg3);
    }
    return $url->get();
} // }} }
// Page  numbering {{{
// Thanks to Nathan Fritz for this.  http://www.netflint.net/
function pagenums($pagenum, $perpage, $totalcount)
{
    global $proj;
    $pagenum = intval($pagenum);
    $perpage = intval($perpage);
    $totalcount = intval($totalcount);

    // Just in case $perpage is something weird, like 0, fix it here:
    if ($perpage < 1) {
        $perpage = $totalcount > 0 ? $totalcount : 1;
    }
    $pages  = ceil($totalcount / $perpage);
    $output = sprintf(eL('page'), $pagenum, $pages);

    if (!($totalcount / $perpage <= 1)) {
        $output .= '<span class="DoNotPrint"> &nbsp;&nbsp;--&nbsp;&nbsp; ';

        $start  = max(1, $pagenum - 4 + min(2, $pages - $pagenum));
        $finish = min($start + 4, $pages);

        if ($start > 1) {
            $url = Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, array('pagenum' => 1))));
            $output .= sprintf('<a href="%s">&lt;&lt;%s </a>', $url, eL('first'));
        }
        if ($pagenum > 1) {
            $url = Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, array('pagenum' => $pagenum - 1))));
            $output .= sprintf('<a id="previous" accesskey="p" href="%s">&lt; %s</a> - ', $url, eL('previous'));
        }

        for ($pagelink = $start; $pagelink <= $finish;  $pagelink++) {
            if ($pagelink != $start) {
                $output .= ' - ';
            }

            if ($pagelink == $pagenum) {
                $output .= sprintf('<strong>%d</strong>', $pagelink);
            } else {
                $url = Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, array('pagenum' => $pagelink))));
                $output .= sprintf('<a href="%s">%d</a>', $url, $pagelink);
            }
        }

        if ($pagenum < $pages) {
            $url =  Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, array('pagenum' => $pagenum + 1))));
            $output .= sprintf(' - <a id="next" accesskey="n" href="%s">%s &gt;</a>', $url, eL('next'));
        }
        if ($finish < $pages) {
            $url = Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, array('pagenum' => $pages))));
            $output .= sprintf('<a href="%s"> %s &gt;&gt;</a>', $url, eL('last'));
        }
        $output .= '</span>';
    }

    return $output;
} // }}}

class Url {
    public $url = '';
    public $parsed;

    public function url($url = '') {
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
// }}}
// }}}
