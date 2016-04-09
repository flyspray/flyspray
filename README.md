[![Build Status](https://travis-ci.org/peterdd/flyspray.svg?branch=master)](https://travis-ci.org/peterdd/flyspray) Flyspray/peterdd

[![Build Status](https://travis-ci.org/Flyspray/flyspray.svg?branch=master)](https://travis-ci.org/Flyspray/flyspray) Flyspray/flyspray

Please do not use Github for your bug reports or feature request. Instead use our live bug tracker at http://bugs.flyspray.org

www.flyspray.org

# Flyspray Bug Tracking System

Flyspray is an uncomplicated, web-based bug and task tracking system.

If you already know all about Flyspray, why wait? Download it now!

If Flyspray is helping your business, please consider helping us by donating a couple of dollars.
Be added to our generous donators page today!

Have you spotted Flyspray in the wild? Does your company or project use Flyspray?
You can send a note to the Mailing List including your project or company name, Flyspray URL (if public),
homepage, and a nice testimonial if you are in the mood and we'll have it added to the list of who is Using Flyspray!

## Installation
http://flyspray.org/manual/install

## Upgrading
1. Create a backup of your files and database
2. Remove all files **but keep**:
   - **flyspray.conf.php**
   - **attachments/** directory
   - **avatars/** directory
   - maybe your own **logo image** if you replaced the flyspray logo
   - maybe **.htaccess** if you use it 
   - maybe own extra customized *.css files like custom_yourproject.css in themes/CleanFS/  (only users of 1.0-dev versions)
3. Copy the new files to the Flyspray directory
4. Make sure flyspray.conf.php is writeable by the webserver.
5. Open http://yourflyspray/setup/ in your webbrowser. It detects the existing installation and you can follow the upgrade steps.

6. Note: Do not forget to press F5 after the upgrade in web browser to reload also cached css-files to see effects of updated CSS-files. They are cached by default for 14 days in the webbrowser.

## Dependencies

### Install php
    Linux: Just use the package manager or one of its frontends of your Linux distribution.
    For instance for Debian based Linux distributions:
    sudo apt-get install php
    
    Windows:
    http://php.net/downloads

#### Install

#### Installing from prepackaged releases that include also needed 3rd party libraries

Choose the matching download for your php version from http://www.flyspray.org/docs/download/
    
    unzip flyspray-1.0*.zip  (or tar xzf flyspray-1.0*.tgz if you prefer .tgz downloads)

Point your webbrowser where use unzipped the download file and follow the configuration instructions. 

#### Installing from source releases via command line

    unzip flyspray-1.0*.zip  (or tar xzf flyspray-1.0*.tar.gz if you prefer the .tar.gz download)
    cd flyspray-1.0*
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    
Point your webbrowser where use unzipped the download file and follow the configuration instructions.
    
#### Windows users can dowload Windows installer on https://getcomposer.org/download/
    If you downloaded the installer, make sure to include it to shell when asked.

#### Download vendors
    Using command lines:
    - 'cd' to the main Flyspray directory
    - Type 'php composer.phar install' to automatically download the vendors
    Users that have it integrated to shell can right click their Flyspray directory and choose "Composer->install" to automatically download the vendors
