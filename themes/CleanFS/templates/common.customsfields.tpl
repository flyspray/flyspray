<!-- add DC 08/2015 A MODIFIER-->
<?php
## TRANSFORM array $rowslistsavble in ARRAY INDEX with KEY lists_name
if (count($rowslistsavble)):
$countlines = -1;
foreach ($rowslistsavble as $rx):
$countlines++;
$listsavble[] = $rx['lists_name'];
endforeach;
endif;

if (count($rows)):
//echo "nb rows:".count($rows).'Liste list_type='.$list_type.'<br>';
//print_r($rows);
?>

<form action="<?php echo Filters::noXSS(CreateURL($do, $list_type, $proj->id)); ?>" method="post">
  <table class="list" id="listTable">
   <thead>
     <tr>
       <th><?php echo Filters::noXSS(L('name')); ?></th>
       <th><?php echo Filters::noXSS(L('catlisttype')); ?></th>
       <th><?php echo Filters::noXSS(L('listavailable')); ?></th>
       <th><?php echo Filters::noXSS(L('defaultvalue')); ?></th>
       <th><?php echo Filters::noXSS(L('forcevalue')); ?></th>
       <th><?php echo Filters::noXSS(L('required')); ?></th>
       
       <th><?php echo Filters::noXSS(L('delete')); ?></th>
       <th></th>
     </tr>
   </thead>
   <tbody>
    <?php
    $countlines = -1;
    foreach ($rows as $row):
    $countlines++;
    //list_type[{$list['list_id']}]
    // var_dump($row);
    //echo $countlines .':'.$list_type.'_name'.':'.$row[$list_type.'_name'].'<br>';
   // echo $countlines .':'.$list_type.'_type'.':'.$row[$list_type.'_type'].'<br>';
   // echo $countlines .':default_value:'.$row['default_value']  .'<br>';
    ?>  
    <tr>
      <td class="first">
        <input id="list_name<?php echo Filters::noXSS($countlines); ?>" 
        class="text" type="text" size="15" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_name']); ?>]"
          value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
      </td> 
       
      <td title="<?php echo Filters::noXSS(L('listcatlisttype')); ?>">
       <select id="list_catlisttype<?php echo Filters::noXSS($countlines); ?>" 
        name="list_catlisttype[<?php echo Filters::noXSS($row[$list_type.'_type']); ?>]">
          <?php echo tpl_options(array(1=>L('list'), 2=>L('date'), 3=>L('text')), $row[$list_type.'_type']); ?>
        </select>
      </td>
      
       <td title="<?php echo Filters::noXSS(L('listavailable')); ?>">
       <select id="list_available<?php echo Filters::noXSS($countlines); ?>" 
       name="list_available[<?php echo Filters::noXSS($row['list_id']); ?>]"> 
       <!-- $listsavblee -->
          <?php echo tpl_options($listsavble,null); ?>
       </select>
      </td>
      
 
     
      <td title="<?php echo Filters::noXSS(L('defaultvalue')); ?>">
       <select id="list_defaultvalue<?php echo Filters::noXSS($countlines); ?>" 
        name="list_defaultvalue[<?php echo Filters::noXSS($row['default_value']); ?>]"> 
        <!-- SELECT list_item_id, item_name FROM flyspray_list_items WHERE show_in_list = 1 AND list_id = '35' ORDER BY list_position -->
       </select>
      </td>
      
      <td title="<?php echo Filters::noXSS(L('forcevalue')); ?>">
      <input id="forcevalue<?php echo Filters::noXSS($row['force_default']); ?>" type="checkbox"
      name="forcevalue[<?php echo Filters::noXSS($row['force_default']); ?>]" value="1" />
      </td>
      
      <td title="<?php echo Filters::noXSS(L('required')); ?>">
      <input id="required<?php echo Filters::noXSS($row['value_required']); ?>" type="checkbox"
      name="required[<?php echo Filters::noXSS($row['value_required']); ?>]" checked="checked" value="1" />
      </td>
     

      <td title="<?php echo Filters::noXSS(L('deletetip')); ?>">
      <input id="delete<?php echo Filters::noXSS($row[$list_type.'_id']); ?>" type="checkbox"
      <?php if ($row['used_in_tasks'] || ($list_type == 'status' && $row[$list_type.'_id'] < 7) || ($list_type == 'resolution' && $row[$list_type.'_id'] == 6)): ?>
      disabled="disabled"
      <?php endif; ?>
      name="delete[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="1" />
      </td>
    
       
    </tr>
    <?php endforeach; ?>
    </tbody>
   <?php if(count($rows)): ?>
    <tr>
      <td colspan="3"></td>
      <td class="buttons">
        <input type="hidden" name="action" value="update_list" />
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('update')); ?></button>
      </td>
    </tr>
    <?php endif; ?>
  </table>
</form>
<hr />
<?php endif; ?>
  <?php if (count($rows)): ?>
  <script type="text/javascript">
        <?php
            echo 'TableControl.create("listTable",{
                controlBox: "controlBox",
                tree: false
            });';
            echo 'new Draggable("controlBox",{
                handle: "grip"
            });';
        ?>
  </script>
  <?php endif; ?>
<hr />
<form action="<?php echo Filters::noXSS(CreateURL($do, $list_type, $proj->id)); ?>" method="post">
  <table class="list">
    <tr>
      <td>
        <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_list" />
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />           
        <?php if ($proj->id): ?>
        <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <?php endif; ?>
        <input type="hidden" name="area" value="<?php echo Filters::noXSS(Req::val('area')); ?>" />
        <input type="hidden" name="do" value="<?php echo Filters::noXSS(Req::val('do')); ?>" />
        <input id="listnamenew" class="text" type="text" size="15" maxlength="40" value="<?php echo Filters::noXSS(Req::val('list_name')); ?>" name="list_name" />
      </td>
      <!-- add on DC -->
      <?php if ($list_type == 'lists'): ?>
      <td title="<?php echo Filters::noXSS(L('listtensetip')); ?>">
        <select id="tensenew" name="<?php echo Filters::noXSS($list_type); ?>_tense">
          <?php echo tpl_options(array(1=>L('Basic'), 3=>L('Category')), 1); ?>

        </select>
      </td>
      <?php endif; ?>
      <td>
        <input id="listpositionnew" class="text" type="text" size="3" maxlength="3" value="<?php echo Filters::noXSS(Req::val('list_position')); ?>" name="list_position" />
      </td>
      <td>
        <input id="showinlistnew" type="checkbox" name="show_in_list" checked="checked" disabled="disabled" />
      </td>
      <td class="buttons">
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('addnew')); ?></button>
      </td>
    </tr>
  </table>
</form>

