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
2. Remove all files **except the attachments/ directory, avatars/ directory and flyspray.conf.php**
3. Copy the new files to the Flyspray directory
4. Make sure flyspray.conf.php is writeable by the webserver.
5. Open http://yourflyspray/setup/ in your webbrowser. It detects the existing installation and you can follow the upgrade steps.

## Dependencies

### Install php
    Linux: Just use the package manager or one of its frontends of your Linux distribution.
    For instance for Debian based Linux distributions:
    sudo apt-get install php
    

    Windows:
    http://php.net/downloads.php


### Install composer
    You can find Composer using the following link: https://getcomposer.org/

#### Installing via command lines

    curl -sS https://getcomposer.org/installer | php

Or if you don't have curl:

    php -r "readfile('https://getcomposer.org/installer');" | php

#### Windows users can dowload Windows installer on https://getcomposer.org/download/
    If you downloaded the installer, make sure to include it to shell when asked.

#### Download vendors
    Using command lines:
    - 'cd' to the main Flyspray directory
    - Type 'php composer.phar install' to automatically download the vendors
    Users that have it integrated to shell can right click their Flyspray directory and choose "Composer->install" to automatically download the vendors
