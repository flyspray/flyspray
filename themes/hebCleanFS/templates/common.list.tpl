<p><?php echo Filters::noXSS(L('listnote')); ?></p>
<?php if (count($rows)): ?>
<div id="controlBox">
    <div class="grip"></div>
    <div class="inner">
        <a href="#" onclick="TableControl.up('listTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/up.png" alt="Up" /></a>
        <a href="#" onclick="TableControl.down('listTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/down.png" alt="Down" /></a>
    </div>
</div>
<?php endif; ?>
<form action="<?php echo Filters::noXSS(CreateURL($do, $list_type, $proj->id)); ?>" method="post">
  <table class="list" id="listTable">
   <thead>
     <tr>
       <th><?php echo Filters::noXSS(L('name')); ?></th>
       <th><?php echo Filters::noXSS(L('order')); ?></th>
       <th><?php echo Filters::noXSS(L('show')); ?></th>
       <?php if ($list_type == 'version'): ?><th><?php echo Filters::noXSS(L('tense')); ?></th><?php endif; ?>
       <th><?php echo Filters::noXSS(L('delete')); ?></th>
     </tr>
   </thead>
   <tbody>
    <?php
    $countlines = -1;
    foreach ($rows as $row):
    $countlines++; ?>
    <tr>
      <td class="first">
        <input id="listname<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" size="15" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
          value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
      </td>
      <td title="<?php echo Filters::noXSS(L('ordertip')); ?>">
        <input id="listposition<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" size="3" maxlength="3" name="list_position[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="<?php echo Filters::noXSS($row['list_position']); ?>" />
      </td>
      <td title="<?php echo Filters::noXSS(L('showtip')); ?>">
        <?php echo tpl_checkbox('show_in_list[' . $row[$list_type.'_id'] . ']', $row['show_in_list'], 'showinlist'.$countlines); ?>

      </td>
      <?php if ($list_type == 'version'): ?>
      <td title="<?php echo Filters::noXSS(L('listtensetip')); ?>">
        <select id="tense<?php echo Filters::noXSS($countlines); ?>" name="<?php echo Filters::noXSS($list_type); ?>_tense[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]">
          <?php echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), $row[$list_type.'_tense']); ?>

        </select>
      </td>
      <?php endif; ?>
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
        <?php if ($list_type == 'version'): ?>
        <input type="hidden" name="action" value="update_version_list" />
        <?php else: ?>
        <input type="hidden" name="action" value="update_list" />
        <?php endif; ?>
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('update')); ?></button>
      </td>
    </tr>
    <?php endif; ?>
  </table>
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
</form>
<hr />
<form action="<?php echo Filters::noXSS(CreateURL($do, $list_type, $proj->id)); ?>" method="post">
  <table class="list">
    <tr>
      <td>
        <?php if ($list_type == 'version'): ?>
        <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_version_list" />
        <?php else: ?>
        <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_list" />
        <?php endif; ?>
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
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
      <?php if ($list_type == 'version'): ?>
      <td title="<?php echo Filters::noXSS(L('listtensetip')); ?>">
        <select id="tensenew" name="<?php echo Filters::noXSS($list_type); ?>_tense">
          <?php echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), 2); ?>

        </select>
      </td>
      <?php endif; ?>
      <td class="buttons">
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('addnew')); ?></button>
      </td>
    </tr>
  </table>
</form>
