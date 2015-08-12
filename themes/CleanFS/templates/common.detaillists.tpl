<!--  add DC 19/02/2015--> 
<?php
///retrieve $lists_id
if(Post::num('lists_id') !=0) $lists_id = Post::num('lists_id');
if(Get::num('lists_id')  !=0) $lists_id = Get::num('lists_id');
?>
<?php if (count($rows)):
//echo "nb rows:".count($rows).'Liste list_type='.$list_type.'projet=>'.$proj->id.'<br>';
//print_r($rows);
?>
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
    //echo $countlines .':'.$row[$countlines].'<br>';
    ?> 
    <tr>
      <!--<td class="first">
        <input type="hidden" name="id[]" value="{$row['list_id']}" />
        <input class="text" type="text" size="15" maxlength="40" name="list_name[{$row['list_id']}]"
          value="{$row['list_name']}" />
      </td>
      -->
      
      <!-- add on DC -->
      
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
      
      <!--
      ADD DC EDIT  
      Modèle : index.php?list_id=51&do=pm&project=32&area=list
     
      <td>
      <a href="<?php /*echo CreateURL('editlists',$row['lists_id'], $proj->id); ?>"><?php echo Filters::noXSS(L('edit')); */?>
      </a>
               
      </td>
     -->  
    </tr>
    <?php endforeach; ?>
    </tbody>
   <?php if(count($rows)): ?>
    <tr>
      <td colspan="3"></td>
      <td class="buttons">
        <?php /*if ($list_type == 'version'): */ ?>
        <!-- <input type="hidden" name="action" value="update_version_list" /> -->
        <?php /*else: */?>
        <input type="hidden" name="action" value="update_list" />
        <?php /*endif; */?>
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="lists_id" value="<?php echo Filters::noXSS($row['lists_id']); ?>" />
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
 <!--//DC détail lists (CUSTOM FIELDS)
 case 'detaillists': $return = $baseurl . 'index.php?area=detaillists&do=pm&lists_id='. $arg1.'&project='.$arg2; break;
 -->
<form action="<?php  echo Filters::noXSS(CreateURL($do, 'lists', $proj->id));  ?>" method="post">
  <table class="list">
    <tr>
      <td>
        <?php /*if ($list_type == 'version'): */?>
        <!-- <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_version_list" /> -->
        <?php /*else: */?>
        <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_list" />
        <?php /*endif; */?>
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="lists_id" value="<?php echo Filters::noXSS($lists_id); ?>" />
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
      
      <?php /* if ($list_type == 'version'): */?>
      <!-- <td title="<?php echo Filters::noXSS(L('listtensetip')); ?>">
        <select id="tensenew" name="<?php echo Filters::noXSS($list_type); ?>_tense">
          <?php /*echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), 2); */?>
        </select>
      </td> -->
      <?php /*endif; */?>  
      <td class="buttons">
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('addnew')); ?></button>
      </td>
    </tr>
  </table>
</form>

