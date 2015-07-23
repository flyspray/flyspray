; <?php die( 'Do not access this page directly.' ); ?>

; This is the Flysplay configuration file. It contains the basic settings
; needed for Flyspray to operate. All other preferences are stored in the
; database itself and are managed directly within the Flyspray admin interface.
; You should consider putting this file somewhere that isn't accessible using
; a web browser, and editing header.php to point to wherever you put this file.



[general]
cookiesalt = "f1s" ; Randomisation value for cookie encoding
output_buffering = "on" ; Available options: "off", "on" and "gzip"
address_rewriting = "0" ; Boolean. 0 = off, 1 = on.
reminder_daemon = "0" ; Boolean. 0 = off, 1 = on.
passwdcrypt = "md5" ; Available options: "crypt", "md5", "sha1"
doku_url = "http://en.wikipedia.org/wiki/" ; URL to your external wiki for [[dokulinks]] in FS
syntax_plugin = "none" ; Plugin name for syntax format for task description and other textarea fields, "none" for the default ckeditor (or any nonexistent plugin folder name), popular alternative: "dokuwiki", see plugins/ directory
update_check = "1" ; Boolean. 0 = off, 1 = on.

securecookies = false ; Boolean false or true. You can set it only to true if you have a HTTPS Flyspray setup fully working with valid SSL/TLS certificate.
; If set to true the Flyspray session cookies should be sent only over HTTPS, never HTTP.
; Check cookie properties within devtools (press F12) of modern (year 2015) webbrowsers.

[database]
dbtype = "mysql"        ; Type of database ("mysql" or "pgsql" are currently supported)
dbhost = "localhost"        ; Name or IP of your database server
dbname = "DBNAME"        ; The name of the database
dbuser = "DBUSER"        ; The user to access the database
dbpass = "DBPASS"        ; The password to go with that username above
dbprefix = "flyspray_" ; Prefix of the Flyspray tables

[attachments]
zip = "application/zip" ; MIME-type for ZIP files

[oauth]
github_secret = ""
github_id = ""
github_redirect = "YOURDOMAIN/index.php?do=oauth&provider=github"
google_secret = ""
google_id = ""
google_redirect = "YOURDOMAIN/index.php?do=oauth&provider=google"
facebook_secret = ""
facebook_id = ""
facebook_redirect = "YOURDOMAIN/index.php?do=oauth&provider=facebook"
microsoft_secret = ""
microsoft_id = ""
microsoft_redirect = "YOURDOMAIN/index.php"
