<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{L('locale')}" xml:lang="{L('locale')}">
  <head>
    <title>{$this->_title}</title>

    <meta name="description" content="Flyspray, a Bug Tracking System written in PHP." />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <?php if(trim($this->get_image('favicon'))): ?>
    <link rel="icon" type="image/png" href="{$this->get_image('favicon')}" />
    <?php endif; ?>
    <link rel="index" id="indexlink" type="text/html" href="{$baseurl}" />
    <?php foreach ($fs->projects as $project): ?>
    <link rel="section" type="text/html" href="{$baseurl}?project={$project[0]}" />
    <?php endforeach; ?>
    <link media="screen" href="{$this->themeUrl()}theme.css" rel="stylesheet" type="text/css" />
    <link media="print"  href="{$this->themeUrl()}theme_print.css" rel="stylesheet" type="text/css" />
    <link rel="alternate" type="application/rss+xml" title="Flyspray RSS 1.0 Feed"
          href="{$baseurl}feed.php?feed_type=rss1&amp;project={$proj->id}" />
    <link rel="alternate" type="application/rss+xml" title="Flyspray RSS 2.0 Feed"
          href="{$baseurl}feed.php?feed_type=rss2&amp;project={$proj->id}" />
	<link rel="alternate" type="application/atom+xml" title="Flyspray Atom 0.3 Feed"
	      href="{$baseurl}feed.php?feed_type=atom&amp;project={$proj->id}" />

    <script type="text/javascript" src="{$baseurl}javascript/prototype/prototype.js"></script>
    <script type="text/javascript" src="{$baseurl}javascript/script.aculo.us/scriptaculous.js"></script>
    <?php if ('index' == $do || 'details' == $do): ?>
        <script type="text/javascript" src="{$baseurl}javascript/{$do}.js"></script>
    <?php endif; ?>
    <?php if ( $do == 'pm' || $do == 'admin'): ?>
        <script type="text/javascript" src="{$baseurl}javascript/tablecontrol.js"></script>
    <?php endif; ?>
    <?php if ( $do == 'depends'): ?>
        <!--[if IE]><script type="text/javascript" src="{$baseurl}javascript/jit/excanvas.js"></script><![endif]-->
        <script type="text/javascript" src="{$baseurl}javascript/jit/jit.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="{$baseurl}javascript/tabs.js"></script>
    <script type="text/javascript" src="{$baseurl}javascript/functions.js"></script>
    <script type="text/javascript" src="{$baseurl}javascript/jscalendar/calendar_stripped.js"></script>
    <script type="text/javascript" src="{$baseurl}javascript/jscalendar/calendar-setup_stripped.js"> </script>
    <script type="text/javascript" src="{$baseurl}javascript/jscalendar/lang/calendar-{substr(L('locale'), 0, 2)}.js"></script>
    <script type="text/javascript" src="{$baseurl}javascript/lightbox/js/lightbox.js"></script>
    <link rel="stylesheet" href="{$baseurl}javascript/lightbox/css/lightbox.css" type="text/css" media="screen" />
    <!--[if IE]>
    <link media="screen" href="{$this->themeUrl()}ie.css" rel="stylesheet" type="text/css" />
    <![endif]-->
    <?php foreach(TextFormatter::get_javascript() as $file): ?>
        <script type="text/javascript" src="{$baseurl}plugins/{$file}"></script>
    <?php endforeach; ?>
  </head>
  <body onload="perms = new Perms('permissions');<?php
        if (isset($_SESSION['SUCCESS']) && isset($_SESSION['ERROR'])):
        ?>window.setTimeout('Effect.Fade(\'mixedbar\', &lbrace;duration:.3&rbrace;)', 10000);<?php
        elseif (isset($_SESSION['SUCCESS'])):
        ?>window.setTimeout('Effect.Fade(\'successbar\', &lbrace;duration:.3&rbrace;)', 8000);<?php
        elseif (isset($_SESSION['ERROR'])):
        ?>window.setTimeout('Effect.Fade(\'errorbar\', &lbrace;duration:.3&rbrace;)', 8000);<?php endif ?>"
				<?php if(isset($_GET['do'])) echo 'class="'.$_GET['do'].'"'; else echo 'class="index"'; ?>
				>

  <div id="container">
    <div id="showparentid" style="display: inline; position: absolute; top: 5px; left: 5px; color: white;">
        <h4 style="display: inline; color: lightgreen;">
        <?php 
            $task_description = '';
            if (isset($task_details) && $task_details['parent_id']) {
                $task_description = L('parenttask') . ': ' . tpl_tasklink($task_details['parent_id'], null, true, array('style' => 'color: lightblue;'));
            }
            else
            if (isset($parent_id) && $parent_id) {
                $task_description = L('parenttask') . ': ' . tpl_tasklink($parent_id, null, true, array('style' => 'color: lightblue;'));
            }
            echo $task_description;
        ?>
        </h4>
    </div>

    <!-- Display title and logo if desired -->
    <h1 id="title"><a href="{$baseurl}">
	<?php if (isset($fs->prefs['logo']) && $fs->prefs['logo'] != '') { ?>
		<img src="{$fs->prefs['logo']}">
	<?php } ?>
	{$proj->prefs['project_title']}
    </a></h1>


    <?php $this->display('links.tpl'); ?>

    <?php if (isset($_SESSION['SUCCESS']) && isset($_SESSION['ERROR'])): ?>
    <div id="mixedbar" class="mixed bar" onclick="this.style.display='none'"><div class="errpadding">{$_SESSION['SUCCESS']}<br />{$_SESSION['ERROR']}</div></div>
    <?php elseif (isset($_SESSION['ERROR'])): ?>
    <div id="errorbar" class="error bar" onclick="this.style.display='none'"><div class="errpadding">{$_SESSION['ERROR']}</div></div>
    <?php elseif (isset($_SESSION['SUCCESS'])): ?>
    <div id="successbar" class="success bar" onclick="this.style.display='none'"><div class="errpadding">{$_SESSION['SUCCESS']}</div></div>
    <?php endif; ?>

    <div id="content">
      <div class="clear"></div>
      <?php $show_message = array(/*'details',*/ 'index', /*'newtask',*/ 'reports', 'depends');
            $actions = explode('.', Req::val('action'));
            if ($proj->prefs['intro_message'] && (in_array($do, $show_message) || in_array(reset($actions), $show_message))): ?>
      <div id="intromessage">{!TextFormatter::render($proj->prefs['intro_message'], false, 'msg', $proj->id,
                               ($proj->prefs['last_updated'] < $proj->prefs['cache_update']) ? $proj->prefs['pm_instructions'] : '')}</div>
      <?php endif; ?>
