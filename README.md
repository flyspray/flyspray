[![Build Status](https://travis-ci.org/peterdd/flyspray.svg?branch=master)](https://travis-ci.org/peterdd/flyspray)

www.flyspray.org

Flyspray Bug Tracking System

Flyspray is an uncomplicated, web-based bug tracking system for assisting with software development. Learn more...
If you already know all about Flyspray, why wait? Download it now!
If Flyspray is helping your business, please consider helping us by donating a couple of dollars. 
Be added to our generous donators page today!

Have you spotted Flyspray in the wild? Does your company or project use Flyspray? 
You can send a note to the Mailing List including your project or company name, Flyspray URL (if public), 
homepage, and a nice testimonial if you are in the mood and we'll have it added to the list of who is Using Flyspray!

Installation:
http://flyspray.org/manual:installation

Upgrading:
Create a backup of your files and database
Remove all files except the attachments directory and flyspray.conf.php
Copy the new files to the Flyspray directory
make sure flyspray.conf.php is writeable by the webserver.
Run the upgrader at http://yourflyspray/setup/upgrade.php


Dependencies:

# Install php
sudo apt-get install php

# Install composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install
