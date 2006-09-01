#!/usr/bin/php -q
<?php
# subversion-close.sh - Subversion<->Flyspray middleware to allow SVN commits to close Flyspray tasks
#
# Version: 0.1 20040720
# Version: 0.2 20040728
# Version: 0.3 20050503
# Author: Jonathan Oxer <jon@ivt.com.au>
# Copyright: 2004 Internet Vision Technologies <www.ivt.com.au>
# Licence: GPL v2 (see <www.fsf.org/licenses/gpl.html>)
#
# subversion-close.sh is called by a post-commit hook in Subversion, which passes it the repo name
# and revision number of the commit. This is typically achieved using an entry like this:
#
#    /usr/share/flyspray/hooks/subversion-close.sh "$REPOS" "$REV"
#
# in 'hooks/post-commit' inside your SVN repository. Once called, subversion-close.sh uses the svnlook
# tool to examine the changelog entry for the revision number it was given. If it finds any lines in
# the changelog of the form:
#
#    closes: #123
#
# it extracts the number from the line and determines which user committed the change. It supports
# multiple bug closures per commit, is case-insensitive, and doesn't care about whitespace around
# the entry (although the single space inside does matter). So it would be ugly but valid to put
# this in a single changelog:
#
#  Closes: #23
#    clOsEs: #24
#   CLOSES: #25
#
# It then attempts to find a file containing the password of that user in its subversion-close
# config directory, named in the form:
#
#    /etc/flyspray/subversion-close/pass-<username>.txt
#
# and containing a plain-text password for that user. For example, if the commit was done by the
# user 'jon' it will look for '/etc/flyspray/subversion-close/pass-jon.txt'. If it does not find a
# password file for the nominated user it falls back to searching for a password file named:
#
#    /etc/flyspray/subversion-close/pass-svn.txt
#
# and continues as if the user that committed the change was 'svn'. It is therefore necessary to
# create a user in Flyspray called 'svn' with sufficient privileges to close tasks, and store the
# password for that user in '/etc/flyspray/subversion-close/pass-svn.txt'. You should also create
# password files for the users who will commonly commit fixes so the notifications and messages in
# Flyspray appear to have been made by the correct user.
#
# It then builds an entry to be used as the 'closure reason' in Flyspray, essentially just the SVN
# changelog with a bit of tweaking.
#
# Finally it uses an XML-RPC library to build a connection to the Flyspray server and close the
# task, submitting the various bits of information such as the changelog entry along with it.
#
# Installation:
#  - mkdir /usr/share/flyspray/hooks and copy this script into it.
#  - mkdir /etc/flyspray/subversion-close and put some pass-<user>.txt files there.
#  - put a call to this script in your repository post-commit hook (repopath/hooks/post-commit)
#     like this: '/usr/share/flyspray/hooks/subversion-close.sh "$REPOS" "$REV"'
#  - put a .htaccess file in /usr/share/flyspray/hooks/ with 'deny from all' in it and make sure
#     Apache honours it.
#
# Security issues:
# Note that the last item is very important! You don't want people to call this script
# directly, so make sure your .htaccess denies everything to the hooks directory.
#
#
# Subversion: subversion.tigris.org
# Flyspray: flyspray.rocks.cc
#

#---------- Config options. Change to suit your installation. ------------------------
$flyspray_baseurl = 'http://localhost/flyspray/';
$resolution_id    = 8; # This is the 'resolution_id' from the 'flyspray_list_resolution' table (8 = 'Fixed');
$tmp_dir          = '/tmp';
$svnlook          = '/usr/bin/svnlook';


#---------- Main prog. No need to change below here. ---------------------------------
$svnrepo = $argv[1];
$svnrev  = $argv[2];

# Debug: Lets see what repo value SVN is sending us
#`echo "$svnrepo" > /tmp/svnrepo`;

chdir($tmp_dir);

# Check if there are any lines in this changelog that have the form 'closes: #123'
$command = "$svnlook info $svnrepo -r $svnrev | grep -i 'closes: #'";
# Debug:
#echo "c: $command\n";
$closes = trim(`$command`);
if(!$closes)
{
	# Looks like there's nothing to do, let's bail
	exit;
}

# If we got here there must be a line in the changelog that closes a bug so
# let's get the whole changelog entry
$command = "$svnlook info $svnrepo -r $svnrev";
# Debug:
#echo "c: $command\n";
$lookdata = trim(`$command`);
# Debug:
#echo "s: $lookdata\n";

# Now we need to chomp on the changelog to get some entries out
$lookdatalines = explode("\n",$lookdata);
$author = array_shift($lookdatalines);
$date   = array_shift($lookdatalines);
$length = array_shift($lookdatalines);

# Check if we know the password for this user (this could be rewritten neatly as a try/if-fail
# test, but I can't be bothered right now).
$passfile = '/etc/flyspray/subversion-close/pass-'.$author.'.txt';
if(file_exists("$passfile"))
{
	$user = $author;
}
else
{
	$user = 'svn';
}
$cookiesfile = $tmp_dir.'/cookies-'.$user.'.txt';
$passfile    = '/etc/flyspray/subversion-close/pass-'.$user.'.txt';
$passwd = trim(`cat $passfile`);


# Let's prepare the actual data we're going to send to Flyspray

# We'll stick the changelog back together without the first couple of lines that we chopped off
$changelog = implode("\n",$lookdatalines);
$close_comment = "Closed by '$user' in Subversion revision $svnrev:
".$changelog;

# Include a copy of the xml-rpc library. This can reside anywhere.
# We're just calling the same copy as the server for convenience.
require_once('/usr/share/flyspray-dev/includes/IXR_Library.inc.php');

# Define the server. Enter the URL of your flyspray installation, with 'remote.php' at the end.
$client = new IXR_Client($flyspray_baseurl . '/remote.php');

# We still don't know what bugs this commit closes, so lets have a look at the 'closes' values
$closes = strtolower("$closes");
$closeslines = explode("\n",$closes);
foreach($closeslines as $closeentry)
{
	$parts = explode("closes: #",$closeentry);
	# Pad with a space so we can reliably explode and get an array, since we don't know
	# if there are spaces after the number. A better alternative would be to chunk_split
	# and build up the number from each character until we run out of digits 0-9.
	$numberpart = $parts[1]." ";
	$numberpart = explode(" ",$numberpart);
	$bugnumber = $numberpart[0];
	# Debug:
	#echo "Processing: $bugnumber\n";

	/**
	 * Close a task. Variables:
	 *   $user
	 *   $passwd
	 *   $task_id
	 *   $reason
	 *   $comment
	 *   $mark100
	 */

	if(!$client->query('fs.closeTask', $user, $passwd, $bugnumber, $resolution_id, $close_comment,1))
	{
		#die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
	}

   // Grab the results from the server
   $response = $client->getResponse();
}

exit;
?>
