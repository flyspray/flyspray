#!/usr/bin/python -u
'''
    ADOdb release build script
'''

import errno
import getopt
import re
import os
from os import path
import shutil
import subprocess
import sys
import tempfile

import updateversion


# ADOdb Repository reference
origin_repo = "git://git.code.sf.net/p/adodb/git adodb-git"
release_branch = "master"
release_prefix = "adodb"

# Directories and files to exclude from release tarballs
exclude_list = (".git*",
                "replicate",
                "scripts",
                # There are no png files in there...
                # "cute_icons_for_site/*.png",
                "hs~*.*",
                "adodb-text.inc.php",
                # This file does not exist in current repo
                # 'adodb-lite.inc.php'
                )

# Command-line options
options = "hfks:"
long_options = ["help", "fresh", "keep"]


def usage():
    print '''Usage: %s [options] version release_path

    Parameters:
        version                 ADOdb version to bundle (e.g. v5.19)
        release_path            Where to save the release tarballs

    Options:
        -h | --help             Show this usage message

        -f | --fresh            Create a fresh clone of the repository
        -k | --keep             Keep build directories after completion
                                (useful for debugging)
''' % (
        path.basename(__file__)
    )
#end usage()


def main():
    # Get command-line options
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
        usage()
        sys.exit(2)

    if len(args) < 2:
        usage()
        print "ERROR: please specify the version and release_path"
        sys.exit(1)

    fresh_clone = False
    cleanup = True

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-f", "--fresh"):
            fresh_clone = True

        elif opt in ("-k", "--keep"):
            cleanup = False

    # Mandatory parameters
    version = updateversion.version_check(args[0])
    release_path = args[1]

    global release_prefix
    release_prefix += version.split(".")[0]

    # Start the build
    print "Building ADOdb release %s into '%s'\n" % (
        version,
        release_path
    )

    if fresh_clone:
        # Create a new repo clone
        print "Cloning a new repository"
        repo_path = tempfile.mkdtemp(prefix=release_prefix + "-",
                                     suffix=".git")
        subprocess.call(
            "git clone %s %s" % (origin_repo, repo_path),
            shell=True
        )
        os.chdir(repo_path)
    else:
        repo_path = '.'

        # Check for any uncommitted changes
        try:
            subprocess.check_output(
                "git diff --exit-code && "
                "git diff --cached --exit-code",
                shell=True
                )
        except:
            print "ERROR: there are uncommitted changes in the repository"
            sys.exit(3)

        # Update the repository
        print "Updating repository in '%s'" % os.getcwd()
        try:
            subprocess.check_output("git fetch", shell=True)
        except:
            print "ERROR: unable to fetch\n"
            sys.exit(3)

    # Check existence of Tag for version in repo, create if not found
    try:
        updateversion.tag_check(version)
    except:
        # Checkout release branch
        subprocess.call("git checkout %s" % release_branch, shell=True)

        # Make sure we're up-to-date
        ret = subprocess.check_output(
            "git status --branch --porcelain",
            shell=True
        )
        if not re.search(release_branch + "$", ret):
            print "\nERROR: branch must be aligned with upstream"
            sys.exit(4)

        # Update the code, create commit and tag
        updateversion.version_set(version)

        # Make sure we don't delete the modified repo
        if fresh_clone:
            cleanup = False

    # Copy files to release dir
    release_tmp_dir = path.join(release_path, release_prefix)
    print "Copying files to '%s'" % release_path
    retry = True
    while True:
        try:
            shutil.copytree(
                repo_path,
                release_tmp_dir,
                ignore=shutil.ignore_patterns(*exclude_list)
            )
            break
        except OSError, err:
            # First try and file exists, try to delete dir
            if retry and err.errno == errno.EEXIST:
                print "WARNING: Directory '%s' exists, delete it and retry" % (
                    release_tmp_dir
                )
                shutil.rmtree(release_tmp_dir)
                retry = False
                continue
            else:
                # We already tried to delete or some other error occured
                raise

    # Create tarballs
    print "Creating release tarballs..."
    release_name = release_prefix + version.split(".")[1]

    os.chdir(release_path)
    subprocess.call(
        "tar czf %s.tgz %s" % (release_name, release_prefix),
        shell=True
    )
    subprocess.call(
        "zip -rq %s.zip %s" % (release_name, release_prefix),
        shell=True
    )

    if cleanup:
        print "Deleting working directories"
        shutil.rmtree(release_tmp_dir)
        if fresh_clone:
            shutil.rmtree(repo_path)
    else:
        print "\nThe following working directories were kept:"
        if fresh_clone:
            print "- '%s' (repo clone)" % repo_path
        print "- '%s' (release temp dir)" % release_tmp_dir
        print "Delete them manually when they are no longer needed."

    # Done
    print "\nADOdb release %s build complete, tarballs saved in '%s'." % (
        version,
        release_tmp_dir
    )

#end main()

if __name__ == "__main__":
    main()
