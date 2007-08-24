; <?php die( 'Do not access this page directly.' ); ?>

; This is the Flysplay configuration file. It contains the basic settings
; needed for Flyspray to operate. All other preferences are stored in the
; database itself and are managed directly within the Flyspray admin interface.
; You should consider putting this file somewhere that isn't accessible using
; a web browser, and editing header.php to point to wherever you put this file.


         
[general]
cookiesalt = "f1s"            ; Randomisation value for cookie encoding
output_buffering = "on"                       ; Available options: "off", "on" and "gzip"
address_rewriting = "0" ; Boolean. 0 = off, 1 = on.
reminder_daemon = "0" ; Boolean. 0 = off, 1 = on.
passwdcrypt = "md5"                                ; Available options: "crypt", "md5", "sha1"
doku_url = "http://en.wikipedia.org/wiki/"      ; URL to your external wiki for [[dokulinks]] in FS
syntax_plugin = "none"                               ; Plugin name for Flyspray's syntax (use any non-existing plugin name for deafult syntax)
update_check = "1"                               ; Boolean. 0 = off, 1 = on.
dot_public = "http://public.research.att.com/~north/cgi-bin/webdot/webdot.cgi" ; URL to a public dot server
dot_path = "" ; Path to the dot executable (for graphs either dot_public or dot_path must be set)
dot_format = "png" ; "png" or "svg"

[database]
dbtype = "mysql"        ; Type of database ("mysql" or "pgsql" are currently supported) 
dbhost = "localhost"        ; Name or IP of your database server
dbname = "DBNAME"        ; The name of the database
dbuser = "DBUSER"        ; The user to access the database
dbpass = "DBPASS"        ; The password to go with that username above
dbprefix = "flyspray_" ; Prefix of the Flyspray tables

[attachments]
zip = "application/zip" ; MIME-type for ZIP files