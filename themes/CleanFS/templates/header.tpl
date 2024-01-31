<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= eL('locale') ?>" xml:lang="<?= eL('locale') ?>">
<head>
<title><?php echo Filters::noXSS($this->_title); ?></title>
<meta name="description" content="Flyspray, a Bug Tracking System written in PHP." />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php if ($fs->prefs['url_rewriting']): ?>
<base href="<?php echo Filters::noXSS($baseurl); ?>" />
<?php endif; ?>
<link rel="icon" href="favicon.ico" />
<?php if(trim($this->get_image('favicon'))): ?>
<link rel="icon" type="image/png" href="<?php echo Filters::noXSS($this->get_image('favicon')); ?>" />
<?php endif; ?>
<link rel="index" id="indexlink" type="text/html" href="<?php echo Filters::noXSS($baseurl); ?>" />
<?php 
/** @todo: This was added around Flyspray 0.9.8 by floele to help search engines find all public visible projects of a Flyspray installation.
 * Probably because the project select is a drop down select, not simple links.
 * What are the alternatives to not list all public projects in the HTML head section of all pages?
 * Maybe only for the configured default page of Flyspray?
 */
foreach ($fs->projects as $project): ?>
<link rel="section" type="text/html" href="<?php echo Filters::noXSS($baseurl); ?>?project=<?php echo Filters::noXSS($project[0]); ?>" />
<?php endforeach; ?>
<link media="screen" href="<?php echo (is_readable(BASEDIR . '/themes/'.$this->_theme.'theme.css')) ? Filters::noXSS($this->themeUrl()) : Filters::noXSS($baseurl).'themes/CleanFS/' ; ?>theme.css" rel="stylesheet" type="text/css" />
<?php
# css hack to fix css3only state switches with ~ in older android browser <4.3 TODO: find webkit version when that issue was fixed.
if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match( '/Android [23]\.\d|Android 4\.[012]/' , $_SERVER['HTTP_USER_AGENT'])):?>
<link rel="stylesheet" type="text/css" media="screen" href="<?= Filters::noXSS($baseurl) ?>themes/CleanFS/oldwebkitsiblingfix.css'; ?>" />
<?php endif; ?>
<link media="print" href="<?php echo (is_readable(BASEDIR . '/themes/'.$this->_theme.'theme_print.css')) ? Filters::noXSS($this->themeUrl()) : Filters::noXSS($baseurl).'themes/CleanFS/' ; ?>theme_print.css" rel="stylesheet" type="text/css" />
<link href="<?= Filters::noXSS($baseurl) ?>themes/CleanFS/font-awesome.min.css" rel="stylesheet" type="text/css" />
<?php 
# include an optional, customized css file for tag styling (all projects, loads even for guests)
if(is_readable(BASEDIR.'/themes/'.$this->_theme.'tags.css')): ?>
<link href="<?php echo Filters::noXSS($this->themeUrl()); ?>tags.css" rel="stylesheet" type="text/css" />
<?php endif; ?>
<?php if($proj->prefs['custom_style'] !=''): ?>
<link media="screen" href="<?php echo Filters::noXSS($this->themeUrl()).$proj->prefs['custom_style']; ?>" rel="stylesheet" type="text/css" />
<?php endif; ?>
<link rel="alternate" type="application/rss+xml" title="Flyspray RSS 1.0 Feed"
  href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />
<link rel="alternate" type="application/rss+xml" title="Flyspray RSS 2.0 Feed"
  href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />
<link rel="alternate" type="application/atom+xml" title="Flyspray Atom 0.3 Feed"
  href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;project=<?php echo Filters::noXSS($proj->id); ?>" />
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/prototype/prototype.js"></script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/script.aculo.us/scriptaculous.js"></script>
<?php if ('index' == $do): ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/index.js"></script>
<?php endif; ?>
<?php if ('details' == $do && $user->can_view_project($proj->id)): ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/details.js"></script>
<?php endif; ?>
<?php 
/** 
 * @todo load only for taskedit page, not task view (currently 'edit=yep' getparam)
 */
if (($do === 'details' or $do === 'newtask') && $proj->prefs['use_tags']): ?>
<link media="screen" rel="stylesheet" type="text/css" href="<?php echo (is_readable(BASEDIR . '/themes/'.$this->_theme.'taskedit.css')) ? Filters::noXSS($this->themeUrl()) : Filters::noXSS($baseurl).'themes/CleanFS/' ; ?>taskedit.css"></link>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/taghelper.js"></script>
<?php endif; ?>
<?php if ( $do == 'pm' || $do == 'admin'): ?>
<link rel="stylesheet" type="text/css" href="<?php echo (is_readable(BASEDIR . '/themes/'.$this->_theme.'adminpm.css')) ? Filters::noXSS($this->themeUrl()) : Filters::noXSS($baseurl).'themes/CleanFS/' ; ?>adminpm.css"></link>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/tablecontrol.js"></script>
<?php endif; ?>
<?php if ( $do == 'depends'): ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/jit/jit.js"></script>
<?php endif; ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/tabs.js"></script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/functions.js"></script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/jscalendar/calendar_stripped.js"></script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/jscalendar/calendar-setup_stripped.js"> </script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/jscalendar/lang/calendar-<?php echo Filters::noXSS(substr(L('locale'), 0, 2)); ?>.js"></script>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/lightbox/js/lightbox.js"></script>
<?php
// load only for page types that have an editor textarea
if (
	isset($conf['general']['syntax_plugin'])
	&& $conf['general']['syntax_plugin'] == 'html'
	&& in_array($do, array('details', 'newtask', 'admin', 'pm', 'editcomment'))
): ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>js/ckeditor/ckeditor.js"></script>
<?php
/** prototype.js spits js-error when enabling hljs.initHighlightingOnLoad();
 * As removal of prototype.js is planned keep the codesnippet highlight stuff here for later turn on.
 */
/*
<link href="<?php echo Filters::noXSS($baseurl); ?>js/ckeditor/plugins/codesnippet/lib/highlight/styles/default.css" rel="stylesheet">
<script src="<?php echo Filters::noXSS($baseurl); ?>js/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
*/
?>
<?php endif; ?>
<link rel="stylesheet" href="<?php echo Filters::noXSS($baseurl); ?>js/lightbox/css/lightbox.css" type="text/css" media="screen" />
<?php foreach(TextFormatter::get_javascript() as $file): ?>
<script type="text/javascript" src="<?php echo Filters::noXSS($baseurl); ?>plugins/<?php echo Filters::noXSS($file); ?>"></script>
<?php endforeach; ?>
<?php if(isset($fs->prefs['captcha_recaptcha']) && $fs->prefs['captcha_recaptcha']
	&& isset($fs->prefs['captcha_recaptcha_sitekey']) && $fs->prefs['captcha_recaptcha_sitekey']!=''
	&& isset($fs->prefs['captcha_recaptcha_secret']) && $fs->prefs['captcha_recaptcha_secret']!=''
): ?>
	<?php
	if ( 
		   ($do=='register')
		|| ($do=='newtask' && $user->isAnon())
	): ?>  
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<?php endif; ?>
<?php endif; ?> 
</head>
<body onload="<?php
        if (isset($_SESSION['SUCCESS']) || isset($_SESSION['ERROR']) || isset($_SESSION['ERRORS'])):
        ?>/* window.setTimeout('Effect.Fade(\'successanderrors\', {duration:.3})', 10000); */
        <?php endif ?>" class="<?php echo (isset($do) ? Filters::noXSS($do) : 'index').' p'.$proj->id; ?>">

    <h1 id="title"><a href="<?php echo Filters::noXSS($baseurl); ?>">
	<?php if($fs->prefs['logo']) { ?><img src="<?php echo Filters::noXSS($baseurl.$fs->prefs['logo']); ?>" /><?php } ?>
	<span><?php if($user->can_select_project($proj->id)){ echo Filters::noXSS($proj->prefs['project_title']); } ?></span>
    </a></h1>
    <?php $this->display('links.tpl'); ?>

	<?php if (isset($_SESSION['SUCCESS']) || isset($_SESSION['ERROR']) || isset($_SESSION['ERRORS'])): ?>
	<div id="successanderrors" onclick="this.style.display='none'">
	<?php endif; ?>
		<?php if(isset($_SESSION['SUCCESS'])): ?><div class="success"><i class="fa fa-check" aria-hidden="true"></i> <?php echo Filters::noXSS($_SESSION['SUCCESS']); ?></div><?php endif; ?>
		<?php if(isset($_SESSION['ERROR'])): ?><div class="error"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo Filters::noXSS($_SESSION['ERROR']); ?></div><?php endif; ?>
		<?php if(isset($_SESSION['ERRORS'])): ?>
		<?php
		foreach(array_keys($_SESSION['ERRORS']) as $e){
			echo '<div class="error"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.eL($e).'</div>';
		}
		?>
		<?php endif; ?>
	<?php if(isset($_SESSION['SUCCESS']) || isset($_SESSION['ERROR']) || isset($_SESSION['ERRORS'])): ?>
	</div>
	<?php endif;?>

<div id="content">
	<?php $show_message = explode(' ', $fs->prefs['pages_welcome_msg']);
	if ($fs->prefs['intro_message'] && ($proj->id == 0 || $proj->prefs['disp_intro']) && (in_array($do, $show_message)) ):?>
	<div id="intromessage"><?php echo TextFormatter::render($fs->prefs['intro_message'], 'msg', $proj->id); ?></div>
	<?php endif; ?>
	<?php if ($proj->id > 0):
	$show_message = explode(' ', $proj->prefs['pages_intro_msg']);
	if ($proj->prefs['intro_message'] && (in_array($do, $show_message))): ?>
	<div id="intromessage"><?php echo TextFormatter::render($proj->prefs['intro_message'], 'msg', $proj->id, ($proj->prefs['last_updated'] < $proj->prefs['cache_update']) ? $proj->prefs['pm_instructions'] : ''); ?></div>
	<?php endif; endif; ?>
