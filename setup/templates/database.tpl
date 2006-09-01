      <div id="right">
         <form action="index.php" method="post" name="database_form">
         {!$message}
         <h1>Database Setup</h1>
         <h2>Database configuration</h2>
         <div class="installBlock">
            <table class="formBlock">
            <tr>
               <td><strong>Install/Upgrade<strong></td>
               <td align="left">
                  {!$db_setup_options}
               </td>
            </tr>
            <tr>
               <td>Host Name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_hostname" value="{$db_hostname}" /></td>
            </tr>
            <tr>
               <td>Database Type</td>
               <td>
               <select name="db_type">
                 {!tpl_options(array_combine(array_keys($databases), array_keys($databases)), $db_type)}
               </select>
               </td>
            </tr>
            <tr>
               <td>Database user name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_username" value="{$db_username}" /></td>
            </tr>
            <tr>
               <td>Database password</td>
               <td align="left"><input class="inputbox" class="password" type="password" name="db_password" value="{$db_password}" /></td>
            </tr>
            <tr>
               <td>Database name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_name" value="{$db_name}" /></td>
            </tr>
            <tr>
               <td>Table prefix</td>
               <td align="left"><input class="inputbox text" type="text" name="db_prefix" value="{$db_prefix}" /></td>
            </tr>
            <tr>
               <td>Drop existing tables?</td>
               <td align="left">{!tpl_checkbox('db_delete', $db_delete, null, 'delete')}
            </tr>
            <tr>
               <td>Backup tables?</td>
               <td align="left">{!tpl_checkbox('db_backup', $db_backup, null, 'backup')}
            </tr>
            </table>
            <p>Follow the steps described below to setup {$product_name}'s Database schema.</p>
            <p>
            1) Select if this is to be a clean <strong>install</strong> or an <strong>upgrade</strong> from
            {$product_name} version 0.9.7.
            </p>
            <p>
            2) Enter the <strong>database hostname</strong> of the server {$product_name} is to be installed on,
            this is usually 'localhost'.
            </p>
            <p>
            3) Enter the <strong>database username and password</strong>. {$product_name} requires that you have a
            database setup with a username and password to install the database schema. If you are not sure about
            these details, please consult with your administrator or web hosting provider.
            </p>
            <p>
            4) Enter the <strong>database table prefix</strong>. If this is the first time you are setting up
            {$product_name}, you can choose the prefix you want the {$product_name}
            tables to have.
            {$product_name} version 0.9.7 had a table prefix of <em>flyspray_</em>, so do not change it if
            you are upgrading.
            </p>
            <p>
            5) <strong>Backing up before Upgrading</strong>. If you are upgrading from {$product_name} version 0.9.7,
            it is strongly advised to make a backup of the databases/tables which may be affected with the username &amp; password you
            provide for this setup. {$product_name} authors will not be held responsibile for any loss of data you
            experience if things go wrong. However we try to do our best to avoid such circumstances.
            </p>
            <p style="font-weight:bold;color:orange;">
            For security measure, you will not be able to <i style="color:#47617B;">drop existing tables</i>
            through this interface unless you select the <i style="color:#47617B;">backup tables</i> checkbox.
            Backed up tables will be stored in the same database with a prefixed timestamp.
            </p>
         </div>
         <div class="clr"></div>
         <h2>Proceed to Administration setup</h2>
         <div class="installBlock">
            <div class="formBlock farRight">
            <input type="hidden" name="action" value="administration" />
            <input class="button" type="submit" name="next" value="Next >>" />
            </div>
            <p>
            Proceed to configure the the Admin parameters.
            </p>
         </div>
         </form>
      </div><!-- end of right -->
      <div class="clr"></div>
