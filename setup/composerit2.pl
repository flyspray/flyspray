#!/usr/bin/perl
use CGI;

$cgi=new CGI;
print $cgi->header();
print '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Flyspray Install - php composer.phar install</title>
<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
</head>
<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
';

print '<h3>Trying to install packages</h3>';
print '<a class="button" style="text-align:center;margin:auto;min-width:300px;" href="index.php">Go to setup page</a>';
chdir('../');
@step2= `export COMPOSER_HOME=. ; php composer.phar install 2>&1`;
print '<pre>&gt; php composer.phar install</pre>';
print '<pre>';
foreach (@step2) {
    print;
}
print '</pre>';
