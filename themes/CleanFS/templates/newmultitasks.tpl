<?php
    if (!isset($supertask_id)) {
        $supertask_id = 0;
    }
?>
<!-- Grab fields wanted for this project so we can only show those we want -->
<?php $fields = explode( ' ', $proj->prefs['visible_fields'] ); ?>

<form enctype="multipart/form-data" action="{CreateUrl('newmultitasks', $proj->id, $supertask_id)}" method="post">
  <input type="hidden" name="supertask_id" value="{$supertask_id}" />
  <input type="hidden" name="action" value="newmultitasks.newmultitasks" />
    <table class="list">
       <thead>
       <tr>
	 <th></th>
         <?php if (in_array('tasktype', $fields)) { ?><th>{L('tasktype')}</th><?php } ?>
         <?php if (in_array('category', $fields)) { ?><th>{L('category')}</th><?php } ?>
         <?php if (in_array('status', $fields)) { ?><th>{L('status')}</th><?php } ?>
         <?php if (in_array('os', $fields)) { ?><th>{L('operatingsystem')}</th><?php } ?>
         <?php if (in_array('severity', $fields)) { ?><th>{L('severity')}</th><?php } ?>
         <?php if (in_array('priority', $fields)) { ?><th>{L('priority')}</th><?php } ?>
         <?php if (in_array('reportedin', $fields)) { ?><th>{L('reportedversion')}</th><?php } ?>
         <?php if (in_array('dueversion', $fields)) { ?><th>{L('dueinversion')}</th><?php } ?>
         <th>{L('summary')}</th>
         <th>{L('details')}</th>
       </tr>
     </thead>
     <tbody id="table">

      <tr id="row">
	<td><input type="image" src="themes/CleanFS/edit_remove.png" onClick="removeRow(this);return false;"/></td>
        <?php if (in_array('tasktype', $fields)) { ?>
	<td>
            <select name="task_type[]" id="tasktype">
              {!tpl_options($proj->listTaskTypes(), Req::val('task_type'))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('category', $fields)) { ?>
	<td>
            <select class="adminlist" name="product_category[]" id="category">
              {!tpl_options($proj->listCategories(), Req::val('product_category'))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('status', $fields)) { ?>
	<td>
            <select id="status" name="item_status[]" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              {!tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED)))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('os', $fields)) { ?>
	<td>
            <select id="os" name="operating_system[]">
              {!tpl_options($proj->listOs(), Req::val('operating_system'))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('severity', $fields)) { ?>
	<td>
            <select id="severity" class="adminlist" name="task_severity[]">
              {!tpl_options($fs->severities, Req::val('task_severity', 2))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('priority', $fields)) { ?>
	<td>
            <select id="priority" name="task_priority[]" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              {!tpl_options($fs->priorities, Req::val('task_priority', 4))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('reportedin', $fields)) { ?>
	<td>
            <select class="adminlist" name="product_version[]" id="reportedver">
              {!tpl_options($proj->listVersions(false, 2), Req::val('product_version'))}
            </select>
	</td>
	<?php } ?>
	<?php if (in_array('dueversion', $fields)) { ?>
	<td>
            <select id="dueversion" name="closedby_version[]" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              <option value="0">{L('undecided')}</option>
              {!tpl_options($proj->listVersions(false, 3),$proj->prefs['default_due_version'], true)}
            </select>
	</td>
	<?php } ?>
	<td>
	    <input type="text" class="text" id="summary" name="item_summary[]" onPaste="pasteMultiLines(this, event);return false"/>
	</td>
	<td>
	    <input type="text" class="text" id="details" name="detailed_desc[]" onPaste="pasteMultiLines(this, event);return false"/>
	</td>
      </tr>

      <tr>
        <td colspan="9"></td>
        <td class="buttons">
          <button class="button positive main" accesskey="s" type="button" style="margin-left:148px" onClick="createRow('','')">new task</button>
        </td>
        <td class="buttons">
          <input type="hidden" name="project_id" value="{$proj->id}" />
          <button class="button positive main" accesskey="s" type="submit">{L('addthistask')}</button>
        </td>
      </tr>


     </tbody>
  </table>
  <script type="text/javascript">

	function createRow(summary, details)
	{
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var clone = rows[0].cloneNode(true);

		var tds = clone.getElementsByTagName("td");
		var length = tds.length;
		tds[length-2].getElementsByTagName("input")[0].value = summary;
		tds[length-1].getElementsByTagName("input")[0].value = details;
		var length = rows.length;
		table.insertBefore(clone, table.lastElementChild);
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
			var row = rows[i];
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

  </script>
</form>
