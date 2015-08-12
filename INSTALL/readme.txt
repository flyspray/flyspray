
database scheme changes :
- modification fields on table flyspray_list_category (contains CATEGORY LIST) 
-create new tables : 
-flyspray_list_lists :      contains lists saved (admin or project mode )
-flyspray_fields :          contains what to display after for( tasks) 
	-lists ( affected to lists already created)  
	-date
	-text

-flyspray_list_standard contains BASIC LIST ( simple list same as CATEGORY LIST without "tree sorts"


About graphical interface : 
I add 2 new tabs in left menu , "ADD LIST" and "EDITION LIST"

not implemented today : 
-only use template CleanFS and don't integrate "rewrite url" module
-all process for  task creation and display , miss another TABLE to save data
