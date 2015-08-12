<!--  add DC 08/2015--> 
<?php
$proj->GetInfosPAramSession($lists_id_g, $lists_name, $catlisttype_g);
if (count($rows)):?>
<form action="<?php  echo Filters::noXSS(CreateURL($do, 'lists', $proj->id)); ?>" method="post">
  <table class="list" id="listTable">
   <thead>
     <tr>
       <th><?php echo Filters::noXSS(L('name')); ?></th>
       <th><?php echo Filters::noXSS(L('order')); ?></th>
       <th><?php echo Filters::noXSS(L('show')); ?></th>
       <th><?php echo Filters::noXSS(L('delete')); ?></th>
       <th></th>
     </tr>
   </thead>
   <tbody>
    <?php
    $countlines = -1;
    foreach ($rows as $row):
    $countlines++;
    //echo "lists_id:".Filters::noXSS($row['lists_id'])."<br>";
    //list_type[{$list['list_id']}]
    //var_dump($row);
    ?> 
    <tr>
      <td class="first">
        <input id="listname<?php echo Filters::noXSS($countlines); ?>" 
        class="text" type="text" size="15" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
          value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
      </td>

      <td title="<?php echo Filters::noXSS(L('ordertip')); ?>">
      <input id="listposition<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" size="3" maxlength="3" name="list_position[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="<?php echo Filters::noXSS($row['list_position']); ?>" />
      </td>
      <td title="<?php echo Filters::noXSS(L('showtip')); ?>">
        <?php echo tpl_checkbox('show_in_list[' . $row[$list_type.'_id'] . ']', $row['show_in_list'], 'showinlist'.$countlines); ?>
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
        <input type="hidden" name="lists_id" value="<?php echo Filters::noXSS($row['lists_id_g']); ?>" />
        <input type="hidden" name="level_lists_type" value="1" /><!-- 1=>L('standard') -->
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
<form action="<?php echo Filters::noXSS(CreateURL($do, $list_type, $proj->id));?>" method="post">
  <table class="list">
    <tr>
      <td>
        <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_list" />
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="lists_id" value="<?php echo Filters::noXSS($lists_id_g); ?>" />
        <input type="hidden" name="level_lists_type" value="1" /><!-- 1=>L('standard') -->
        <?php if ($proj->id): ?>
        <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <?php endif; ?>
        <input type="hidden" name="area" value="<?php echo Filters::noXSS(Req::val('area')); ?>" />
        <input type="hidden" name="do" value="<?php echo Filters::noXSS(Req::val('do')); ?>" />
        <input id="listnamenew" class="text" type="text" size="15" maxlength="40" value="<?php echo Filters::noXSS(Req::val('list_name')); ?>" name="list_name" />
      </td>
 
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

