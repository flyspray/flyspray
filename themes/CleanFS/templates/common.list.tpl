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
		<a href="#" id="controlBoxUp" title="Up"><span class="fas fa-square-caret-up fa-3x"></span></a>
		<a href="#" id="controlBoxDown" title="Down"><span class="fas fa-square-caret-down fa-3x"></span></a>
	</div>
</div>
<?php endif; ?>
<?php echo tpl_form(Filters::noXSS(createURL($do, $list_type, $proj->id))); ?>
<table class="list" id="listTable">
<colgroup>
	<?php if ($list_type == 'tag'): ?>
	<col class="ctag" />
	<?php endif; ?>
	<col class="cname" />
	<?php if ($list_type == 'tag'): ?>
	<col class="cclasses" />
	<?php endif; ?>
	<col class="corder" />
	<col class="cshow" />
	<?php if ($list_type == 'version'): ?>
	<col class="ctense" />
	<?php endif; ?>
	<col class="cdelete" />
	<col class="cusage" />
</colgroup>
<?php if ($do=='pm'): ?>
<thead>
<tr>
	<th colspan="<?= $tcols ?>"><?= eL('systemvalues') ?></th>
</tr>
</thead>
<thead>
<tr>
<?php if ($list_type == 'tag'): ?>
	<th>ID</th>
<?php endif; ?>
	<th class="text"><?= eL('name') ?></th>
<?php if ($list_type == 'tag'): ?>
	<th class="text">CSS Classes</th>
<?php endif; ?>
	<th><?= eL('order') ?></th>
	<th><?= eL('show') ?></th>
<?php if ($list_type == 'version'): ?>
	<th><?= eL('tense') ?></th>
<?php endif; ?>
	<th><?= eL('delete'); ?></th>
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
<?php if ($list_type == 'tag'): ?>
	<td>
		<?php echo tpl_tag($row['tag_id'], true); ?>
	</td>
<?php endif; ?>
	<td<?= ($list_type!='tag') ? ' class="'.$classtype.' '.$class.'"':'' ?>>
		<?= ($list_type=='tag') ? tpl_tag($row['tag_id']) : Filters::noXSS($row[$list_type.'_name']); ?>
	</td>
<?php if ($list_type == 'tag'): ?>
	<td>
		<?php echo Filters::noXSS($row['class']); ?>
	</td>
<?php endif; ?>
	<td title="<?= eL('ordertip') ?>">
		<?php echo Filters::noXSS($row['list_position']); ?>
	</td>
	<td title="<?= eL('showtip') ?>">
		<?php echo $row['show_in_list']; ?>
	</td>
<?php if ($list_type == 'version'): ?>
	<td title="<?= eL('listtensetip') ?>">
		<?php echo $row[$list_type.'_tense']; ?>
	</td>
<?php endif; ?>
	<td><span class="fas fa-ban"></span></td>
	<td>
		<?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?>
	</td>
</tr>
<?php
	endforeach;
?>
<?php else: ?>
<tr>
	<td colspan="<?= $tcols ?>">
		<?= eL('novalues') ?>
	</td>
</tr>
<?php endif; ?>
</thead>
<?php endif; ?>
<thead>
<tr>
	<th colspan="<?= $tcols ?>">
		<?= $do=='pm' ? eL('projectvalues') : eL('systemvalues') ?>
	</th>
</tr>
</thead>
<thead>
<tr>
<?php if ($list_type == 'tag'): ?>
	<th>ID</th>
<?php endif; ?>
	<th class="text"><?= eL('name') ?></th>
<?php if ($list_type == 'tag'): ?>
	<th class="text" title="CSS Classes or a #rgb or #rrggbb color. For instance #c00 for a red background">
		CSS Classes or #rgb
	</th>
<?php endif; ?>
	<th><?= eL('order') ?></th>
	<th><?= eL('show') ?></th>
<?php if ($list_type == 'version'): ?>
	<th><?= eL('tense') ?></th>
<?php endif; ?>
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
<?php if ($list_type == 'tag'): ?>
	<td>
		<?php echo tpl_tag($row['tag_id'], true); ?>
	</td>
<?php endif; ?>
	<td>
		<input id="listname<?php echo Filters::noXSS($countlines); ?>" class="text fi-stretch" type="text" maxlength="40" name="list_name[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]"
			value="<?php echo Filters::noXSS($row[$list_type.'_name']); ?>" />
	</td>
<?php if ($list_type == 'tag'): ?>
	<td>
		<input id="listclass<?php echo Filters::noXSS($countlines); ?>" class="text fi-stretch" type="text" maxlength="40" name="list_class[<?php echo Filters::noXSS($row['tag_id']); ?>]"
			value="<?php echo Filters::noXSS($row['class']); ?>" />
	</td>
<?php endif; ?>
	<td title="<?= eL('ordertip') ?>" class="narrow">
		<input id="listposition<?php echo Filters::noXSS($countlines); ?>" class="text ta-e fi-xxx-small" type="text" maxlength="3" name="list_position[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="<?php echo Filters::noXSS($row['list_position']); ?>" />
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
	<td title="<?= eL('nodeletetip') ?>">
		<span class="fas fa-ban"></span>
	</td>
<?php else: ?>
	<td title="<?= eL('deletetip') ?>">
		<input id="delete<?php echo Filters::noXSS($row[$list_type.'_id']); ?>" type="checkbox" name="delete[<?php echo Filters::noXSS($row[$list_type.'_id']); ?>]" value="1" />
	</td>
<?php endif; ?>

	<td>
		<?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if(count($rows)): ?>
<div class="buttons">
	<input type="hidden" name="action" value="update_list" />
	<input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
	<input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<button type="submit"><?= eL('update') ?></button>
</div>
<?php endif; ?>


<?php if (count($rows)): ?>
	<script type="text/javascript" src="js/commonlist.js"></script>
<?php endif; ?>
</form>

<fieldset>
	<legend><?= eL('addnew'); ?></legend>

	<?php echo tpl_form(Filters::noXSS(createURL($do, $list_type, $proj->id))); ?>

	<ul class="form_elements">
		<li>
			<label for="listnamenew"><?= eL('name'); ?></label>
			<div class="valuewrap">
				<input id="listnamenew" type="text" class="fi-large" maxlength="40" value="" name="list_name" />
			</div>
		</li>
<?php if ($list_type == 'tag'): ?>
		<li>
			<label for="listclassnew">CSS Classes or #rgb</label>
			<div class="valuewrap">
				<input id="listclassnew" type="text" class="fi-large" value="" name="list_class" />
			</div>
		</li>
<?php endif; ?>
		<li>
			<label for="listpositionnew"><?= eL('order'); ?></label>
			<div class="valuewrap">
				<input id="listpositionnew" class="fi-xxx-small ta-e" type="text" maxlength="3" value="" name="list_position" />
			</div>
		</li>
		<li>
			<label><?= eL('show'); ?></label>
			<div class="valuewrap">
				<input id="showinlistnew" type="checkbox" name="show_in_list" checked="checked" disabled="disabled" />
			</div>
		</li>
<?php if ($list_type == 'version'): ?>
		<li>
			<label for="tensenew"><?= eL('listtensetip'); ?></label>
			<div class="valuewrap">
				<select id="tensenew" name="<?php echo Filters::noXSS($list_type); ?>_tense">
				<?php echo tpl_options(array(1=>L('past'), 2=>L('present'), 3=>L('future')), 2); ?>
				</select>
			</div>
		</li>
<?php endif; ?>
	</ul>

	<div class="buttons">
<?php if ($list_type == 'version'): ?>
		<input type="hidden" id="action" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_version_list" />
<?php else: ?>
		<input type="hidden" id="action" name="action" value="<?php echo Filters::noXSS($do); ?>.add_to_list" />
<?php endif; ?>
		<input type="hidden" name="list_type" value="<?php echo Filters::noXSS($list_type); ?>" />
<?php if ($proj->id): ?>
		<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
<?php endif; ?>
		<input type="hidden" name="area" value="<?php echo Filters::noXSS(Req::val('area')); ?>" />
		<input type="hidden" name="do" value="<?php echo Filters::noXSS($do); ?>" />
		<input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
		<button type="submit" class="positive"><?= eL('addnew'); ?></button>
	</div>
	</form>
</fieldset>
