<p><?php echo Filters::noXSS(L('listnote')); ?></p>
<?php if ($do=='pm'):
# show systemwide settings for this list on project setting page too ..
?>
<h3><?php echo Filters::noXSS(L('systemvalues'));
# TODO: should be h2 tag, h1 tag for page type title, and project/flyspray title not a h1-tag in the header.
?></h3>
<table class="list" id="idtablesys">
<colgroup>
    <?php if ($list_type == 'tag'): ?><col class="ctag" /><?php endif; ?>
    <col class="cname" />
    <?php if ($list_type == 'tag'): ?><col class="cclasses" /><?php endif; ?>
    <col class="corder" />
    <col class="cshow" />
    <?php if ($list_type == 'version'): ?><col class="ctense" /><?php endif; ?>
    <col class="cdelete" />
    <col class="cusage" />
</colgroup>
<thead>
<tr>
    <?php if ($list_type == 'tag'): ?><th>ID</th><?php endif; ?>
    <th><?php echo Filters::noXSS(L('name')); ?></th>
    <?php if ($list_type == 'tag'): ?><th>CSS Classes</th><?php endif; ?>
    <th><?php echo Filters::noXSS(L('order')); ?></th>
    <th><?php echo Filters::noXSS(L('show')); ?></th>
    <?php if ($list_type == 'version'): ?><th><?php echo Filters::noXSS(L('tense')); ?></th><?php endif; ?>
    <th>&nbsp;</th>
    <th><?php echo Filters::noXSS(L('usedintasks')); ?></th>
</tr>
</thead>
<tbody>
<?php if (isset($sysrows) && count($sysrows)): ?>
<?php
$syscountlines=-1;
foreach ($sysrows as $row):
$syscountlines++;
?>
<tr>
    <?php if ($list_type == 'tag'): ?><td><i class="tag t<?php echo $row[$list_type.'_id']; ?>"><?php echo $row[$list_type.'_id']; ?></i></td><?php endif; ?>
    <td class="first"><?php echo Filters::noXSS($row[$list_type.'_name']); ?></td>
    <?php if ($list_type == 'tag'): ?><td><?php echo Filters::noXSS($row['class']); ?></td><?php endif; ?>
    <td title="<?php echo Filters::noXSS(L('ordertip')); ?>"><?php echo Filters::noXSS($row['list_position']); ?></td>
    <td title="<?php echo Filters::noXSS(L('showtip')); ?>"><?php echo $row['show_in_list']; ?></td>
    <?php if ($list_type == 'version'): ?><td title="<?php echo Filters::noXSS(L('listtensetip')); ?>"><?php echo $row[$list_type.'_tense']; ?></td><?php endif; ?>
    <td>&nbsp;</td>
    <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="<?php echo $list_type=='version' ? 5 : 4; ?>"><?php echo Filters::noXSS(L('novalues')); ?></td></tr>
<?php endif; ?>
</tbody>
</table>
<?php endif; ?>
<h3><?php echo $do=='pm' ? Filters::noXSS(L('projectvalues')) : Filters::noXSS(L('systemvalues'));
# TODO: should be h2 tag, h1 tag for page type title, and project/flyspray title not a h1-tag in the header.
?></h3>
<?php if (count($rows)): ?>
<div id="controlBox">
    <div class="grip"></div>
    <div class="inner">
        <a href="#" onclick="TableControl.up('listTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/up.png" alt="Up" /></a>
        <a href="#" onclick="TableControl.down('listTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/down.png" alt="Down" /></a>
    </div>
</div>
<?php endif; ?>
<?php echo tpl_form(Filters::noXSS(CreateURL($do, $list_type, $proj->id))); ?>
<table class="list" id="listTable">
<colgroup>
    <?php if ($list_type == 'tag'): ?><col class="ctag" /><?php endif; ?>
    <col class="cname" />
    <?php if ($list_type == 'tag'): ?><col class="cclasses" /><?php endif; ?>
    <col class="corder" />
    <col class="cshow" />
    <?php if ($list_type == 'version'): ?><col class="ctense" /><?php endif; ?>
    <col class="cdelete" />
    <col class="cusage" />
</colgroup>
<thead>
<tr>
    <?php if ($list_type == 'tag'): ?><th>ID</th><?php endif; ?>
    <th><?php echo Filters::noXSS(L('name')); ?></th>
    <?php if ($list_type == 'tag'): ?><th>CSS Classes</th><?php endif; ?>
    <th><?php echo Filters::noXSS(L('order')); ?></th>
    <th><?php echo Filters::noXSS(L('show')); ?></th>
    <?php if ($list_type == 'version'): ?><th><?php echo Filters::noXSS(L('tense')); ?></th><?php endif; ?>
    <th><?php echo Filters::noXSS(L('delete')); ?></th>
    <th><?php echo Filters::noXSS(L('usedintasks')); ?></th>
</tr>
</thead>
<tbody>
<?php
    $countlines = -1;
    foreach ($rows as $row):
    $countlines++;
?>
<tr>
    <?php if ($list_type == 'tag'): ?><td><i class="tag t<?php echo $row[$list_type.'_id']; ?><?php echo isset($row['class']) ? ' '.Filters::noXSS($row['class']) : ''; ?>"><?php echo $row[$list_type.'_id']; ?></i></td><?php endif; ?>
    <td>
        <input id="listname<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
          value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
    </td>
    <?php if ($list_type == 'tag'): ?>
    <td>
        <input id="listclass<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="40" name="list_class[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
          value="<?php echo Filters::noXSS($row['class']); ?>" />
    </td>
    <?php endif; ?>
    <td title="<?php echo Filters::noXSS(L('ordertip')); ?>">
        <input id="listposition<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="3" name="list_position[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="<?php echo Filters::noXSS($row['list_position']); ?>" />
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
      <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    <?php if(count($rows)): ?>
    <tfoot>
    <tr>
      <td colspan="3"></td>
      <td colspan="2" class="buttons">
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
    </tfoot>
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
<?php echo tpl_form(Filters::noXSS(CreateURL($do, $list_type, $proj->id))); ?>
<table class="list">
<colgroup>
    <col class="cname" />
    <col class="corder" />
    <col class="cshow" />
    <?php if ($list_type == 'version'): ?><col class="ctense" /><?php endif; ?>
    <col class="cdelete" />
</colgroup>
<tbody>
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
        <input type="hidden" name="do" value="<?php echo Filters::noXSS($do); ?>" />
        <input id="listnamenew" placeholder="<?php echo Filters::noXSS(L('name')); ?>" class="text" type="text" maxlength="40" value="<?php echo Filters::noXSS(Req::val('list_name')); ?>" name="list_name" autofocus />
      </td>
      <td>
        <input id="listpositionnew" placeholder="<?php echo Filters::noXSS(L('order')); ?>" class="text" type="text" maxlength="3" value="<?php echo Filters::noXSS(Req::val('list_position')); ?>" name="list_position" />
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
        <button type="submit" class="positive"><?php echo Filters::noXSS(L('addnew')); ?></button>
      </td>
    </tr>
</tbody>
</table>
</form>
