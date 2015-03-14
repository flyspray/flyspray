#!/usr/bin/perl
use CGI;

$cgi=new CGI;
print $cgi->header();
print '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Flyspray Install - curl -sS https://getcomposer.org/installer | php</title>
<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
</head>
<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
';
print '<h3>Trying to load composer stuff</h3><pre>&gt; curl -sS https://getcomposer.org/installer | php</pre>';
chdir('../');
@step1= `curl -sS https://getcomposer.org/installer | php`;
print '<h3>Step 1 result</h3><br/><pre>';
foreach (@step1) {
    print;
}
print '</pre>';

print '<a class="button" style="text-align:center;margin:auto;display:block;max-width:300px;font-size:2em;" href="composerit2.pl">Next Step</a>';
