<p><?php echo Filters::noXSS(L('listnote')); ?></p>
<?php
if ($do=='pm'):
?>
<h3><?php echo Filters::noXSS(L('categoriesglobal')); ?></h3>
<table id="idtablesys" class="listtable listtable_category">
<thead>
<tr>
	<th class="name"><?php echo Filters::noXSS(L('name')); ?></th>
	<th class="owner"><?php echo Filters::noXSS(L('owner')); ?></th>
	<th class="show"><?php echo Filters::noXSS(L('show')); ?></th>
	<th class="usage"><?php echo Filters::noXSS(L('usedintasks')); ?></th>
	<th class="actions"><?php echo Filters::noXSS(L('delete')); ?></th>
</tr>
</thead>
<tbody>
<?php
	if (isset($sysrows) && count($sysrows)):
		$syscountlines=-1;

		foreach ($sysrows as $row):
			$syscountlines++;
?>
<tr>
	<td class="listitem_name"><span class="depthmark"><?php echo str_repeat('&rarr;', $row['depth']); ?></span><?php echo Filters::noXSS($row['category_name']); ?></td>
	<td class="listitem_owner"><?php echo ($row['category_owner'] == 0)? '&mdash;' : Filters::noXSS($row['category_owner']); ?></td>
	<td class="listitem_show" title="<?php echo Filters::noXSS(L('showtip')); ?>"><span class="fas fa-<?php echo ($row['show_in_list'] == 1 ? 'check' : 'ban'); ?>"></span></td>
	<td class="listitem_usage"><?php echo $row['used_in_tasks'] > 0 ? $row['used_in_tasks'] : '&mdash;'; ?></td>
	<td class="listitem_actions"><span class="fas fa-ban"></span></td>
</tr>
<?php
		endforeach;
?>
<?php
	else:
?>
<tr><td colspan="5"><?php echo Filters::noXSS(L('novalues')); ?></td></tr>
<?php
	endif;
?>
</tbody>
</table>
<?php
endif;
?>
<h3><?php echo $do=='pm' ? Filters::noXSS(L('categoriesproject')) : Filters::noXSS(L('categoriesglobal')); ?></h3>
<?php
$countlines = -1;
$categories = $proj->listCategories($proj->id, false, false, false);

if ( count($categories) ){
	$root = $categories[0];
	unset($categories[0]);

	if ((count($categories)*6 + 4) > ini_get('max_input_vars')) {
?>
<div class="error">A category tree update of this size requires sending more than <strong><?= ini_get('max_input_vars') ?></strong> key-value pairs (PHP ini setting <code>max_input_vars</code>).
But the current size for an update requires up to <?= (count($categories)*6 + 4) ?> key-value pairs.
Increase <code>max_input_vars</code> PHP ini setting before doing any update of this category tree! Otherwise you maybe get a messed up category tree in the database!</div>
<?php
	}
} else{
	$root=array();
}

if (count($categories)) : ?>
<div id="controlBox">
	<div class="grip"></div>
		<div class="inner">
		<a href="#" id="controlBoxUp" onclick="TableControl.up('catTable'); return false;" title="Up">
			<span class="fas fa-square-caret-up fa-3x">
		</a>
		<a href="#" id="controlBoxLeft" onclick="TableControl.shallower('catTable'); return false;" title="Left">
			<span class="fas fa-square-caret-left fa-3x">
		</a>
		<a href="#" id="controlBoxRight" onclick="TableControl.deeper('catTable'); return false;" title="Right">
			<span class="fas fa-square-caret-right fa-3x">
		</a>
		<a href="#" id="controlBoxDown" href="#" onclick="TableControl.down('catTable'); return false;" title="Down">
			<span class="fas fa-square-caret-down fa-3x">
		</a>
	</div>
</div>
<?php
endif;

echo tpl_form(Filters::noXSS(CreateURL($do, 'cat', $proj->id)));
?>
<table id="catTable" class="listtable interactive listtable_category">
<thead>
<tr>
	<th class="name"><?php echo Filters::noXSS(L('name')); ?></th>
	<th class="owner"><?php echo Filters::noXSS(L('owner')); ?></th>
	<th class="show"><?php echo Filters::noXSS(L('show')); ?></th>
	<th class="usage"><?php echo Filters::noXSS(L('usedintasks')); ?></th>
	<th class="actions"><?php echo Filters::noXSS(L('delete')); ?></th>
</tr>
</thead>
<tbody>
<?php
foreach ($categories as $row):
	$countlines++;
?>
<tr class="depth<?php echo Filters::noXSS($row['depth']); ?>">
	<td class="listitem_name">
		<input type="hidden" name="lft[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['lft']); ?>" />
		<input type="hidden" name="rgt[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['rgt']); ?>" />
		<span class="depthmark"><?php echo str_repeat('&rarr;', intval($row['depth'])); ?></span>
		<input id="categoryname<?php echo Filters::noXSS($countlines); ?>" class="text fi-stretch" type="text" maxlength="40" name="list_name[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['category_name']); ?>" />
	</td>
	<td title="<?php echo Filters::noXSS(L('categoryownertip')); ?>" class="listitem_owner">
		<?php echo tpl_userselect('category_owner[' . $row['category_id'] . ']' . $countlines, $row['category_owner'], 'categoryowner' . $countlines); ?>
	</td>
	<td title="<?php echo Filters::noXSS(L('listshowtip')); ?>" class="listitem_show">
		<?php echo tpl_checkbox('show_in_list[' . $row['category_id'] . ']', $row['show_in_list'], 'showinlist'.$countlines); ?>
	</td>
	<td class="listitem_usage">
		<?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:'&mdash;'; ?>
	</td>
	<td title="<?php echo Filters::noXSS(L('listdeletetip')); ?>" class="listitem_actions">
		<input id="delete<?php echo Filters::noXSS($row['category_id']); ?>" type="checkbox"
		<?php if ($row['used_in_tasks']): ?>disabled="disabled"<?php endif; ?>
		name="delete[<?php echo Filters::noXSS($row['category_id']); ?>]" value="1" />
	</td>
</tr>
<?php
endforeach;
?>
</tbody>
</table>

<?php if($countlines > -1): ?>
<div class="buttons">
	<input type="hidden" name="action" value="update_category" />
	<input type="hidden" name="list_type" value="category" />
	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<button type="submit"><?php echo Filters::noXSS(L('update')); ?></button>
</div>
<?php endif;

if (count($categories)): ?>
<script type="text/javascript">
	<?php
		echo 'TableControl.create("catTable",{
			controlBox: "controlBox",
			tree: true,
			spreadActiveClass: true
		});';
		echo 'new Draggable("controlBox",{
			handle: "grip"
		});';
	?>
</script>
<?php endif; ?>
</form>

<fieldset>
	<legend><?= eL('addnew'); ?></legend>

	<?php echo tpl_form(Filters::noXSS(CreateURL($do, 'cat', $proj->id))); ?>

	<ul class="form_elements">
		<li>
			<label for="listnamenew"><?= eL('name'); ?></label>
			<div class="valuewrap">
				<input id="listnamenew" class="text fi-large" type="text" maxlength="40" name="list_name" />
			</div>
		</li>
		<li>
			<label for="categoryownernew"><?= eL('owner'); ?></label>
			<div class="valuewrap">
				<?php echo tpl_userselect('category_owner', Req::val('category_owner'), 'categoryownernew'); ?>
				<span class="note"><?php echo Filters::noXSS(L('categoryownertip')); ?></span>
			</div>
		</li>
		<li>
			<label for="parent_id"><?= eL('parent'); ?></label>
			<div class="valuewrap">
				<select id="parent_id" name="parent_id">
				<option value="<?php echo Filters::noXSS($root['category_id']); ?>"><?php echo Filters::noXSS(L('notsubcategory')); ?></option>
				<?php echo tpl_options($proj->listCategories($proj->id, false), Req::val('parent_id')); ?>
				</select>
				<span class="note"><?php echo Filters::noXSS(L('categoryparenttip')); ?></span>
			</div>
		</li>
	</ul>

	<div class="buttons">
		<input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_category" />
		<input type="hidden" name="area" value="<?php echo Filters::noXSS(Req::val('area')); ?>" />
		<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
		<button type="submit"><?php echo Filters::noXSS(L('addnew')); ?></button>
	</div>
	</form>
</fieldset>
