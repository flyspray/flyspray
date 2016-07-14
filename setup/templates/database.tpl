<div>
<form action="index.php" method="post" name="database_form">
      <?php echo $message; ?>

      <h1><?php echo Filters::noXSS(L('databasesetup')); ?></h1>
      <h2><?php echo Filters::noXSS(L('databaseconfiguration')); ?><?php echo Filters::noXSS($version); ?></h2>
      <div class="installBlock">
            <table class="formBlock" style="width:auto">
            <tr>
               <td><?php echo Filters::noXSS(L('databasehostname')); ?></td>
               <td align="left"><input required="required" class="inputbox text" type="text" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" /></td>
               <td><?php echo L('databasehostnamehint'); ?></td>
            </tr>
            <tr>
               <td><?php echo Filters::noXSS(L('databasetype')); ?></td>
               <td><select name="db_type">
                 <?php echo tpl_options(array_combine(array_map(create_function('$x', 'return $x[2];'), $databases), array_keys($databases)), $db_type); ?>
               </select>
               </td>
               <td><?php echo L('databasetypehint'); ?></td>
            </tr>
            <tr>
               <td><?php echo Filters::noXSS(L('databasename')); ?></td>
               <td align="left"><input class="inputbox text" type="text" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" /></td>
               <td><?php echo L('databasenamehint'); ?></td>
            </tr>
            <tr>
               <td><?php echo Filters::noXSS(L('databaseusername')); ?></td>
               <td align="left"><input class="inputbox text" type="text" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" /></td>
               <td rowspan="2"><?php echo L('databaseusernamehint'); ?></td>
            </tr>
            <tr>
               <td><?php echo Filters::noXSS(L('databasepassword')); ?></td>
               <td align="left"><input class="inputbox" class="password" type="password" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" /></td>
            </tr>
            <tr>
               <td><?php echo Filters::noXSS(L('tableprefix')); ?></td>
               <td align="left"><input class="inputbox text" type="text" maxlength="10" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" /></td>
               <td><?php echo L('tableprefixhint'); ?></td>
            </tr>
            </table>

            <input type="hidden" name="action" value="administration" />
            <button class="button" type="submit" name="next" value="next"><?php echo Filters::noXSS(L('proceedtoadmin')); ?></button>
      </div>
</form>
</div>
