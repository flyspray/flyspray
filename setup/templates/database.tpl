      <div id="right">
         <form action="index.php" method="post" name="database_form">
         <?php echo $message; ?>

         <h1>Database Setup</h1>
         <h2>Database configuration</h2>
         <div class="installBlock">
            <table class="formBlock">
            <tr>
               <td><strong>Install<strong></td>
               <td align="left">
                  <?php echo Filters::noXSS($version); ?>

               </td>
            </tr>
            <tr>
               <td>Host Name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" /></td>
            </tr>
            <tr>
               <td>Database Type</td>
               <td>
               <select name="db_type">
                 <?php echo tpl_options(array_combine(array_map(create_function('$x', 'return $x[2];'), $databases), array_keys($databases)), $db_type); ?>

               </select>
               </td>
            </tr>
            <tr>
               <td>Database user name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" /></td>
            </tr>
            <tr>
               <td>Database password</td>
               <td align="left"><input class="inputbox" class="password" type="password" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" /></td>
            </tr>
            <tr>
               <td>Database name</td>
               <td align="left"><input class="inputbox text" type="text" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" /></td>
            </tr>
            <tr>
               <td>Table prefix</td>
               <td align="left"><input class="inputbox text" type="text" maxlength="10" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" /></td>
            </tr>
            </table>
            <p>Follow the steps described below to setup <?php echo Filters::noXSS($product_name); ?>'s Database schema.</p>
            <p>
            1) Enter the <strong>database hostname</strong> of the server <?php echo Filters::noXSS($product_name); ?> is to be installed on,
            this is usually 'localhost'.
            </p>
            <p>
            2) Choose the <strong>database type</strong>. If you have both the choice between MySQL and MySQLi, use the latter one.
            </p>
            <p>
            3) Enter the <strong>database username and password</strong>. <?php echo Filters::noXSS($product_name); ?> requires that you have a
            database setup with a username and password to install the database schema. If you are not sure about
            these details, please consult with your administrator or web hosting provider.
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
