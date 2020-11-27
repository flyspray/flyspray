<div>
<form action="index.php" method="post" name="database_form">
      <?php echo $message; ?>

      <h1><?= eL('databasesetup') ?></h1>
      <h2><?= eL('databaseconfiguration') ?><?php echo Filters::noXSS($version); ?></h2>
      <div class="installBlock">
            <table class="formBlock" style="width:auto">
            <tr>
               <td><?= eL('databasehostname') ?></td>
               <td align="left"><input required="required" type="text" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" /></td>
               <td><?= L('databasehostnamehint') ?></td>
            </tr>
            <tr>
               <td><?= eL('databasetype') ?></td>
               <td><select name="db_type">
                 <?php echo tpl_options(array_combine(array_map(function($x){return $x[2];}, $databases), array_keys($databases)), $db_type); ?>
               </select>
               </td>
               <td><?= L('databasetypehint') ?></td>
            </tr>
            <tr>
               <td><?= eL('databasename') ?></td>
               <td align="left"><input required="required" type="text" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" /></td>
               <td><?= L('databasenamehint') ?></td>
            </tr>
            <tr>
               <td><?= eL('databaseusername') ?></td>
               <td align="left"><input required="required" type="text" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" /></td>
               <td rowspan="2"><?= L('databaseusernamehint') ?></td>
            </tr>
            <tr>
               <td><?= eL('databasepassword') ?></td>
               <td align="left"><input type="password" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" /></td>
            </tr>
            <tr>
               <td><?= eL('tableprefix') ?></td>
               <td align="left"><input type="text" maxlength="10" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" /></td>
               <td><?= L('tableprefixhint') ?></td>
            </tr>
            </table>

            <input type="hidden" name="action" value="administration" />
            <button class="button" type="submit" name="next" value="next"><?= eL('proceedtoadmin') ?></button>
      </div>
</form>
</div>
