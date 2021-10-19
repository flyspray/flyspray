<p><?= eL('listnote') ?></p>
<?php
$tcols=5;
if($list_type == 'version') {
    $tcols++;
}
if($list_type == 'tag') {
    $tcols=$tcols+2;
}
?>
<?php if (count($rows)): ?>
<div id="controlBox">
    <div class="grip"></div>
    <div class="inner">
        <a href="#" id="controlBoxUp"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/up.png" alt="Up" /></a>
        <a href="#" id="controlBoxDown"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/down.png" alt="Down" /></a>
    </div>
</div>
<?php endif; ?>
<?php echo tpl_form(Filters::noXSS(createURL($do, $list_type, $proj->id))); ?>
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
<?php if ($do=='pm'): ?>
<thead>
<tr><th colspan="<?= $tcols ?>"><?= eL('systemvalues') ?></th></tr>
</thead>
<thead>
<tr>
    <?php if ($list_type == 'tag'): ?><th>ID</th><?php endif; ?>
    <th><?= eL('name') ?></th>
    <?php if ($list_type == 'tag'): ?><th>CSS Classes</th><?php endif; ?>
    <th><?= eL('order') ?></th>
    <th><?= eL('show') ?></th>
    <?php if ($list_type == 'version'): ?><th><?= eL('tense') ?></th><?php endif; ?>
    <th>&nbsp;</th>
    <th><?= eL('usedintasks') ?></th>
</tr>
</thead>
<thead id="globalentries">
<?php if (isset($sysrows) && count($sysrows)): ?>
<?php
$syscountlines=-1;
foreach ($sysrows as $row):
$syscountlines++;
$classtype=''; $class='';
switch ($list_type){
        case 'tag':
                $classtype='tag';
                $class='t';
                break;
        case 'tasktype':
                $classtype='task_tasktype';
                $class='typ'.$row[$list_type.'_id'];
                break;
        case 'status':
                $classtype='task_status';
                $class='sta'.$row[$list_type.'_id'];
                break;
        default:
                $classtype='task_'.$list_type;
                $class=substr($list_type, 0, 3).$row[$list_type.'_id'];
}
?>
<tr>
    <?php if ($list_type == 'tag'): ?><td><?php echo tpl_tag($row['tag_id'], true); ?></td><?php endif; ?>
    <td<?= ($list_type!='tag') ? ' class="'.$classtype.' '.$class.'"':'' ?>><?= ($list_type=='tag') ? tpl_tag($row['tag_id']) : Filters::noXSS($row[$list_type.'_name']); ?></td>
    <?php if ($list_type == 'tag'): ?><td><?php echo Filters::noXSS($row['class']); ?></td><?php endif; ?>
    <td title="<?= eL('ordertip') ?>"><?php echo Filters::noXSS($row['list_position']); ?></td>
    <td title="<?= eL('showtip') ?>"><?php echo $row['show_in_list']; ?></td>
    <?php if ($list_type == 'version'): ?><td title="<?= eL('listtensetip') ?>"><?php echo $row[$list_type.'_tense']; ?></td><?php endif; ?>
    <td>&nbsp;</td>
    <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="<?= $tcols ?>"><?= eL('novalues') ?></td></tr>
<?php endif; ?>
</thead>
<?php endif; ?>
<thead>
<tr><th colspan="<?= $tcols ?>"><?= $do=='pm' ? eL('projectvalues') : eL('systemvalues') ?></th></tr>
</thead>
<thead>
<tr>
    <?php if ($list_type == 'tag'): ?><th>ID</th><?php endif; ?>
    <th><?= eL('name') ?></th>
    <?php if ($list_type == 'tag'): ?><th title="CSS Classes or a #rgb or #rrggbb color. For instance #c00 for a red background">CSS Classes or #rgb</th><?php endif; ?>
    <th><?= eL('order') ?></th>
    <th><?= eL('show') ?></th>
    <?php if ($list_type == 'version'): ?><th><?= eL('tense') ?></th><?php endif; ?>
    <th><?= eL('delete') ?></th>
    <th><?= eL('usedintasks') ?></th>
</tr>
</thead>
<tbody>
<?php
    $countlines = -1;
    foreach ($rows as $row):
    $countlines++;
?>
<tr<?= ($list_type == 'resolution' && $row[$list_type.'_id'] == RESOLUTION_DUPLICATE ) ? ' class="nodelete" title="fixed duplicate resolution status"':'' ?>>
    <?php if ($list_type == 'tag'): ?><td><?php echo tpl_tag($row['tag_id'], true); ?></td><?php endif; ?>
    <td>
        <input id="listname<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
          value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
    </td>
    <?php if ($list_type == 'tag'): ?>
    <td>
        <input id="listclass<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="40" name="list_class[<?php echo Filters::noXSS($row['tag_id']); ?>]"
          value="<?php echo Filters::noXSS($row['class']); ?>" />
    </td>
    <?php endif; ?>
    <td title="<?= eL('ordertip') ?>">
        <input id="listposition<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="3" name="list_position[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="<?php echo Filters::noXSS($row['list_position']); ?>" />
      </td>
      <td title="<?= eL('showtip') ?>">
        <?php echo tpl_checkbox('show_in_list[' . $row[$list_type.'_id'] . ']', $row['show_in_list'], 'showinlist'.$countlines); ?>

      </td>
      <?php if ($list_type == 'version'): ?>
      <td title="<?= eL('listtensetip') ?>">
        <select id="tense<?php echo Filters::noXSS($countlines); ?>" name="<?php echo Filters::noXSS($list_type); ?>_tense[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]">
          <?php echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), $row[$list_type.'_tense']); ?>
        </select>
      </td>
      <?php endif; ?>

      <?php if ($row['used_in_tasks'] || ($list_type == 'status' && $row[$list_type.'_id'] < 7) || ($list_type == 'resolution' && $row[$list_type.'_id'] == RESOLUTION_DUPLICATE ) ): ?>
      <td title="<?= eL('nodeletetip') ?>"></td>
      <?php else: ?>
      <td title="<?= eL('deletetip') ?>"><input id="delete<?php echo Filters::noXSS($row[$list_type.'_id']); ?>" type="checkbox" name="delete[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="1" /></td>
      <?php endif; ?>

      <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    <?php if(count($rows)): ?>
    <tfoot>
    <tr>
      <td colspan="<?= ($tcols-2) ?>"></td>
      <td colspan="2" class="buttons">
        <input type="hidden" name="action" value="update_list" />
        <input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit"><?= eL('update') ?></button>
      </td>
    </tr>
    </tfoot>
    <?php endif; ?>
  </table>
  <?php if (count($rows)): ?>
  <script type="text/javascript" src="js/commonlist.js"></script>
  <?php endif; ?>
</form>
<hr />
<?php echo tpl_form(Filters::noXSS(createURL($do, $list_type, $proj->id))); ?>
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
        <input id="listnamenew" placeholder="<?= eL('name') ?>" class="text" type="text" maxlength="40" value="<?php echo Filters::noXSS(Req::val('list_name')); ?>" name="list_name" autofocus />
      </td>
      <td>
        <input id="listpositionnew" placeholder="<?= eL('order') ?>" class="text" type="text" maxlength="3" value="<?php echo Filters::noXSS(Req::val('list_position')); ?>" name="list_position" />
      </td>
      <td>
        <input id="showinlistnew" type="checkbox" name="show_in_list" checked="checked" disabled="disabled" />
      </td>
      <?php if ($list_type == 'version'): ?>
      <td title="<?= eL('listtensetip') ?>">
        <select id="tensenew" name="<?php echo Filters::noXSS($list_type); ?>_tense">
          <?php echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), 2); ?>
        </select>
      </td>
      <?php endif; ?>
      <td class="buttons">
        <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
        <button type="submit" class="positive"><?= eL('addnew') ?></button>
      </td>
    </tr>
</tbody>
</table>
</form>
