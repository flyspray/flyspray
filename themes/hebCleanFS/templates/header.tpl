<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo Filters::noXSS(L('locale')); ?>" xml:lang="<?php echo Filters::noXSS(L('locale')); ?>">
  <head>
    <title><?php echo Filters::noXSS($this->_title); ?></title>

    <meta name="description" content="Flyspray, a Bug Tracking System written in PHP." />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <?php if(trim($this->get_image('favicon'))): ?>
    <link rel="icon" type="image/png" href="<?php echo Filters::noXSS($this->get_image('favicon')); ?>" />
    <?php endif; ?>
    <link rel="index" id="indexlink" type="text/html" href="<?php echo Filters::noXSS($baseurl); ?>" />
    <?php foreach ($fs->projects as $project): ?>
    <link rel="section" type="text/html" href="<?php echo Filters::noXSS($baseurl); ?>?project=<?php echo Filters::noXSS($project[0]); ?>" />
    <?php endforeach; ?>
    <link media="screen" href="<?php echo Filters::noXSS($this->themeUrl()); ?>theme.css" rel="stylesheet" type="text/css" />
    <link media="print"  href="<?php echo Filters::noXSS($this->themeUrl()); ?>theme_print.css" rel="stylesheet" type="text/css" />
    <link rel="alternate" type="application/rss+xml" title="Flyspray RSS 1.0 Feed"
          href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />
    <link rel="alternate" type="application/rss+xml" title="Flyspray RSS 2.0 Feed"
          href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Flyspray Atom 0.3 Feed"
	      href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />

    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/prototype/prototype.js"></script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/script.aculo.us/scriptaculous.js"></script>
    <?php if ('index' == $do || 'details' == $do): ?>
        <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/<?php echo Filters::noXSS($do); ?>.js"></script>
    <?php endif; ?>
    <?php if ( $do == 'pm' || $do == 'admin'): ?>
        <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/tablecontrol.js"></script>
    <?php endif; ?>
    <?php if ( $do == 'depends'): ?>
        <!--[if IE]><script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/jit/excanvas.js"></script><![endif]-->
        <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/jit/jit.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/tabs.js"></script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/functions.js"></script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/jscalendar/calendar_stripped.js"></script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/jscalendar/calendar-setup_stripped.js"> </script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/jscalendar/lang/calendar-<?php echo Filters::noXSS(substr(L('locale'), 0, 2)); ?>.js"></script>
    <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>javascript/lightbox/js/lightbox.js"></script>
    <link rel="stylesheet" href="<?php echo Filters::noXSS($baseurl); ?>javascript/lightbox/css/lightbox.css" type="text/css" media="screen" />
    <!--[if IE]>
    <link media="screen" href="<?php echo Filters::noXSS($this->themeUrl()); ?>ie.css" rel="stylesheet" type="text/css" />
    <![endif]-->
    <?php foreach(TextFormatter::get_javascript() as $file): ?>
        <script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>plugins/<?php echo Filters::noXSS($file); ?>"></script>
    <?php endforeach; ?>
  </head>
  <body onload="perms = new Perms('permissions');<?php
        if (isset($_SESSION['SUCCESS']) && isset($_SESSION['ERROR'])):
        ?>window.setTimeout('Effect.Fade(\'mixedbar\', {duration:.3})', 10000);<?php
        elseif (isset($_SESSION['SUCCESS'])):
        ?>window.setTimeout('Effect.Fade(\'successbar\', {duration:.3})', 8000);<?php
        elseif (isset($_SESSION['ERROR'])):
        ?>window.setTimeout('Effect.Fade(\'errorbar\', {duration:.3})', 8000);<?php endif ?>"
				<?php if(isset($_GET['do'])) echo 'class="'.$_GET['do'].'"'; else echo 'class="index"'; ?>
				>

  <div id="container">
    <!-- Remove this to remove the logo -->
    <h1 id="title"><a href="<?php echo Filters::noXSS($baseurl); ?>"><?php echo Filters::noXSS($proj->prefs['project_title']); ?></a></h1>

    <?php $this->display('links.tpl'); ?>

    <?php if (isset($_SESSION['SUCCESS']) && isset($_SESSION['ERROR'])): ?>
    <div id="mixedbar" class="mixed bar" onclick="this.style.display='none'"><div class="errpadding"><?php echo Filters::noXSS($_SESSION['SUCCESS']); ?><br /><?php echo Filters::noXSS($_SESSION['ERROR']); ?></div></div>
    <?php elseif (isset($_SESSION['ERROR'])): ?>
    <div id="errorbar" class="error bar" onclick="this.style.display='none'"><div class="errpadding"><?php echo Filters::noXSS($_SESSION['ERROR']); ?></div></div>
    <?php elseif (isset($_SESSION['SUCCESS'])): ?>
    <div id="successbar" class="success bar" onclick="this.style.display='none'"><div class="errpadding"><?php echo Filters::noXSS($_SESSION['SUCCESS']); ?></div></div>
    <?php endif; ?>

    <div id="content">
      <div class="clear"></div>
      <?php $show_message = array(/*'details',*/ 'index', /*'newtask',*/ 'reports', 'depends');
            $actions = explode('.', Req::val('action'));
            if ($proj->prefs['intro_message'] && (in_array($do, $show_message) || in_array(reset($actions), $show_message))): ?>
      <div id="intromessage"><?php echo TextFormatter::render($proj->prefs['intro_message'], false, 'msg', $proj->id,
                               ($proj->prefs['last_updated'] < $proj->prefs['cache_update']) ? $proj->prefs['pm_instructions'] : ''); ?></div>
      <?php endif; ?>
