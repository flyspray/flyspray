<?php
    if (!isset($supertask_id)) {
        $supertask_id = 0;
    }
    $field_num = 3;
?>
<!-- Grab fields wanted for this project so we can only show those we want -->
<?php $fields = explode( ' ', $proj->prefs['visible_fields'] ); ?>

<div id="intromessage">Tips for bulk importing:<br>

    1. Copy and paste from an excel spreadsheet or CSV by pasting one entire column.<br>
    2. Currently you can only paste Summary and Details.<br>
    3. There are suggestions when you assign to someone, and to no-one if there is no matched name.

</div>
<form enctype="multipart/form-data" action="<?php echo Filters::noXSS(CreateUrl('newmultitasks', $proj->id, $supertask_id)); ?>" method="post">
  <input type="hidden" name="supertask_id" value="<?php echo Filters::noXSS($supertask_id); ?>" />
  <input type="hidden" name="action" value="newmultitasks.newmultitasks" />

    <table class="list">
       <thead>
       <tr>
	 <th></th>
         <?php if (in_array('tasktype', $fields)) { ?><th><?php echo Filters::noXSS(L('tasktype')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('category', $fields)) { ?><th><?php echo Filters::noXSS(L('category')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('status', $fields)) { ?><th><?php echo Filters::noXSS(L('status')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('os', $fields)) { ?><th><?php echo Filters::noXSS(L('operatingsystem')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('severity', $fields)) { ?><th><?php echo Filters::noXSS(L('severity')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('priority', $fields)) { ?><th><?php echo Filters::noXSS(L('priority')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('reportedin', $fields)) { ?><th><?php echo Filters::noXSS(L('reportedversion')); ?></th><?php $field_num++;} ?>
         <?php if (in_array('dueversion', $fields)) { ?><th><?php echo Filters::noXSS(L('dueinversion')); ?></th><?php $field_num++;} ?>
	 <?php if ($user->perms('modify_all_tasks')): ?><?php if (in_array('assignedto', $fields)) { ?><th><?php echo Filters::noXSS(L('assignedto')); ?></th><?php $field_num++;} ?><?php endif; ?>
         <th><?php echo Filters::noXSS(L('summary')); ?></th>
         <th><?php echo Filters::noXSS(L('details')); ?></th>
       </tr>
     </thead>
     <tbody id="table">
      <button class="button positive main" accesskey="s" type="button" onClick="Apply()">Apply first line</button>
      <tr id="row">
	<td><input type="image" src="themes/CleanFS/img/red/x_alt_24x24.png" onClick="removeRow(this);return false;"/></td>
        <?php if (in_array('tasktype', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select name="task_type[]" id="tasktype">
              <?php echo tpl_options($proj->listTaskTypes(), Req::val('task_type')); ?>

            </select>
	</td>

          <!-- Category-->
	<?php if (in_array('category', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select class="adminlist" name="product_category[]" id="category">
              <?php echo tpl_options($proj->listCategories(), Req::val('product_category')); ?>

            </select>
	</td>

          <!-- Status-->
	<?php if (in_array('status', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select id="status" name="item_status[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED))); ?>
            </select>
	</td>

          <!-- OS-->
	<?php if (in_array('os', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select id="os" name="operating_system[]">
              <?php echo tpl_options($proj->listOs(), Req::val('operating_system')); ?>

            </select>
	</td>

          <!-- Severity-->
	<?php if (in_array('severity', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select id="severity" class="adminlist" name="task_severity[]">
              <?php echo tpl_options($fs->severities, Req::val('task_severity', 2)); ?>

            </select>
	</td>

          <!-- Priority-->
	<?php if (in_array('priority', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select id="priority" name="task_priority[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($fs->priorities, Req::val('task_priority', 4)); ?>
            </select>
	</td>

          <!-- Reported Version-->
	<?php if (in_array('reportedin', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select class="adminlist" name="product_version[]" id="reportedver">
              <?php echo tpl_options($proj->listVersions(false, 2), Req::val('product_version')); ?>

            </select>
	</td>

          <!-- Due Version -->
	<?php if (in_array('dueversion', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } ?>
            <select id="dueversion" name="closedby_version[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <option value="0"><?php echo Filters::noXSS(L('undecided')); ?></option>
              <?php echo tpl_options($proj->listVersions(false, 3),$proj->prefs['default_due_version'], false); ?>
            </select>
	</td>

          <!-- Assigned To -->
	<?php if ($user->perms('modify_all_tasks')){ ?><?php if (in_array('assignedto', $fields)) { ?>
	<td>
	<?php } else { ?>
	<td style="display:none">
	<?php } echo tpl_userselect('assigned_to[0]', Req::val('assigned_to[0]'), 'find_user_0'); ?>
	</td>
	<?php } ?>
	<td>
	    <input type="text" class="text" size="30" id="summary" name="item_summary[]" onPaste="pasteMultiLines(this, event);return false"/>
	</td>
	<td>
	    <input type="text" class="text" size="20" id="details" name="detailed_desc[]" onkeydown="return TabandCreate(this, event);" onPaste="pasteMultiLines(this, event);return false"/>
	</td>
      </tr>

      <tr>
        <td colspan="<?php echo $field_num-2;?>"></td>
        <td class="buttons">
          <button class="button positive main" accesskey="s" type="button" onClick="createRow('','')">Add more rows</button>
        </td>
        <td class="buttons">
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <button class="button positive main" accesskey="s" type="submit">Add tasks</button>
        </td>
      </tr>


     </tbody>
  </table>
  <script type="text/javascript">

	var index = 0;
	function createRow(summary, details)
	{
		index++;
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var clone = rows[0].cloneNode(true);

		var tds = clone.getElementsByTagName("td");
		var length = tds.length;
		tds[length-2].getElementsByTagName("input")[0].value = summary;
		tds[length-1].getElementsByTagName("input")[0].value = details;
		tds[length-3].getElementsByTagName("input")[0].value = "";
		tds[length-3].getElementsByTagName("script")[0].innerHTML = "";
		var res = tds[length-3].innerHTML.replace(/assigned_to\[0\]/g, "assigned_to[" + index + "]");
		res = res.replace(/find_user_0/g, "find_user_" + index);
		tds[length-3].innerHTML = res;
		table.insertBefore(clone, table.lastElementChild);
		showstuff("assigned_to[" + index + "]_complete");
		new Ajax.Autocompleter(tds[length-3].getElementsByTagName("input")[0].id, tds[length-3].getElementsByTagName("span")[0].id, "<?php echo Filters::noXSS($baseurl); ?>js/callbacks/usersearch.php", null);
	}
	function removeRow(elem)
	{
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var length = rows.length;
		if(length <= 2)
			return false;
		for(var i = 0; i < length -1; i++)
		{
			if(rows[i] == elem.parentNode.parentNode) {
				table.deleteRow(i);
				break;
			}
		}
	}
	function pasteMultiLines(elem, e)
	{

		if(e && e.clipboardData && e.clipboardData.getData) {
			var strs = e.clipboardData.getData("text/plain").split("\n");
			var table = document.getElementById("table");
			var rows = table.getElementsByTagName("tr");
			for(var i = 0; i < rows.length-1; i++)
			{
				if(rows[i] == elem.parentNode.parentNode)
					break;
			}
			var index;
			if(elem.id == "summary")
				index = 2;
			else
				index = 1;
			var k = 0;
			for(var j = i; j < rows.length-1 && k < strs.length; j++, k++)
			{
				var tds = rows[j].getElementsByTagName("td");
				var length = tds.length;
				tds[length-index].getElementsByTagName("input")[0].value = strs[k];
			}
			for(; k < strs.length; k++)
			{
				if(index == 2)
					createRow(strs[k], "");
				else
					createRow("", strs[k]);
			}
		}
	}
	function Apply()
	{
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var fields = rows[0].getElementsByTagName("td");
		for(var i = 1; i < rows.length-1; i++)
		{
			var tds = rows[i].getElementsByTagName("td");
			for(var j = 1; j < tds.length; j++)
			{
				var input = tds[j].getElementsByTagName("input");
				var select = tds[j].getElementsByTagName("select");
				if(input != null && input.length > 0)
				{
					input[0].value = fields[j].getElementsByTagName("input")[0].value;
				}
				if(select != null && select.length > 0)
				{
					select[0].value = fields[j].getElementsByTagName("select")[0].value;
				}
			}
		}
	}
	function TabandCreate(elem, e)
	{
		if(e.keyCode != 9)
			return true;
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var length = rows.length;
		var parent = elem.parentNode.parentNode;
		if(parent == rows[length-2])
			createRow('','');
		parent.nextElementSibling.getElementsByTagName("input")[1].focus();
		return false;
	}
  </script>
</form>
