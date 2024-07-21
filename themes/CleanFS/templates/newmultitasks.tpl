<?php
if (!isset($supertask_id)) {
	$supertask_id = 0;
}
?>
<!-- Grab fields wanted for this project so we can only show those we want -->
<?php $fields = explode(' ', $proj->prefs['visible_fields']); ?>

<div id="intromessage"><?= L('hintforbulkimport') ?></div>
<?php echo tpl_form(Filters::noXSS(createUrl('newmultitasks', $proj->id, $supertask_id))); ?>
	<input type="hidden" name="supertask_id" value="<?php echo Filters::noXSS($supertask_id); ?>" />
	<input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
	<input type="hidden" name="action" value="newmultitasks.newmultitasks" />
	<button id="addrow" class="button" accesskey="a" type="button" onClick="createRow('','')"><?= eL('addmorerows') ?></button>
	<button class="button" accesskey="f" type="button" onClick="Apply()"><?= eL('applyfirstline') ?></button>
	<table class="list">
	<thead>
	<tr>
		<th></th>
		<?php if (in_array('tasktype', $fields)) { ?>
		<th><?= eL('tasktype') ?></th>
		<?php } ?>
		<?php if (in_array('category', $fields)) { ?>
		<th><?= eL('category') ?></th>
		<?php } ?>
		<?php if (in_array('status', $fields)) { ?>
		<th><?= eL('status') ?></th>
		<?php } ?>
		<?php if (in_array('os', $fields)) { ?>
		<th><?= eL('operatingsystem') ?></th>
		<?php } ?>
		<?php if (in_array('severity', $fields)) { ?>
		<th><?= eL('severity') ?></th>
		<?php } ?>
		<?php if (in_array('priority', $fields)) { ?>
		<th><?= eL('priority') ?></th>
		<?php } ?>
		<?php if (in_array('reportedin', $fields)) { ?>
		<th><?= eL('reportedversion') ?></th>
		<?php } ?>
		<?php if (in_array('dueversion', $fields)) { ?>
		<th><?= eL('dueinversion') ?></th>
		<?php } ?>
		<?php if ($user->perms('modify_all_tasks')): ?>
		<?php if (in_array('assignedto', $fields)):  ?>
		<th><?= eL('assignedto') ?></th>
		<?php endif; ?>
		<?php endif; ?>
		<th><?= eL('summary') ?></th>
		<th><?= eL('details') ?></th>
	</tr>
	</thead>
	<tbody id="table">
	<tr data-rownum="0">
		<td><button class="button" type="button" onClick="removeRow(this);return false;"><span class="fas fa-xmark fa-lg"></span></button></td>
		<?php if (in_array('tasktype', $fields)): ?>
		<td>
			<select name="task_type[]" id="tasktype_0">
			<?php echo tpl_options($proj->listTaskTypes(), Req::val('task_type')); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Category-->
		<?php if (in_array('category', $fields)): ?>
		<td>
			<select class="adminlist" name="product_category[]" id="category_0">
			<?php echo tpl_options($proj->listCategories(), Req::val('product_category')); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Status-->
		<?php if (in_array('status', $fields)): ?>
		<td>
			<select id="status_0" name="item_status[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
			<?php echo tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED))); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- OS-->
		<?php if (in_array('os', $fields)): ?>
		<td>
			<select id="os_0" name="operating_system[]">
			<?php echo tpl_options($proj->listOs(), Req::val('operating_system')); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Severity-->
		<?php if (in_array('severity', $fields)): ?>
		<td>
			<select id="severity_0" class="adminlist" name="task_severity[]">
			<?php echo tpl_options($fs->severities, Req::val('task_severity', 2)); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Priority-->
		<?php if (in_array('priority', $fields)): ?>
		<td>
			<select id="priority_0" name="task_priority[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
			<?php echo tpl_options($fs->priorities, Req::val('task_priority', 4)); ?>
			</select>
		</td>
		<?php endif ?>

		<!-- Reported Version-->
		<?php if (in_array('reportedin', $fields)): ?>
		<td>
			<select class="adminlist" name="product_version[]" id="reportedver_0">
			<?php echo tpl_options($proj->listVersions(false, 2), Req::val('product_version')); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Due Version -->
		<?php if (in_array('dueversion', $fields)): ?>
		<td>
			<select id="dueversion_0" name="closedby_version[]" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
			<option value="0"><?= eL('undecided') ?></option>
			<?php echo tpl_options($proj->listVersions(false, 3),$proj->prefs['default_due_version'], false); ?>
			</select>
		</td>
		<?php endif; ?>

		<!-- Assigned To -->
		<?php if ($user->perms('modify_all_tasks') && in_array('assignedto', $fields)): ?>
		<td>
			<?php echo tpl_userselect('assigned_to_0', Req::val('assigned_to_0'), 'find_user_0'); ?>
		</td>
		<?php endif; ?>

		<td>
			<input type="text" class="text" size="30" id="summary_0" name="item_summary[]" onPaste="pasteMultiLines(this, event);return false"/>
		</td>
		<td>
			<input type="text" class="text" size="20" id="details_0" name="detailed_desc[]" onkeydown="return TabandCreate(this, event);" onPaste="pasteMultiLines(this, event);return false"/>
		</td>
	</tr>
	</tbody>
	</table>

	<div class="buttons" >
		<button class="button positive" accesskey="s" type="submit"><?= eL('addtasks') ?></button>
	</div>

<script type="text/javascript">
	var index = 0;
	function createRow(summary, details)
	{
		table = document.getElementById("table");
		h = table.querySelectorAll('#table tr:first-child [id]');
		nextid = Number(table.lastElementChild.dataset.rownum) + 1;
		clone = table.firstElementChild.cloneNode(true);
		clone.dataset.rownum = nextid;

		newrow = new DocumentFragment();
		newrow.append(clone);

		ids = Array.from(newrow.querySelectorAll('[id]'));
		ids.forEach(function(e, i) {
			e.id = e.id.replaceAll(/_\d+$/g, '_' + nextid);
		});

		// initialize the new row
		newrow.querySelector('input[name^=item_summary]').value = summary;
		newrow.querySelector('input[name^=detailed_desc]').value = details;
		newrow.querySelector('input[name^=assigned_to]').name = 'assigned_to[' + nextid + ']';
		newrow.querySelector('span[id^=assigned_to]').id = 'assigned_to_' + nextid + '_complete';

		newrow_script = newrow.querySelector('script');
		var res = newrow_script.innerHTML.replaceAll(/(assigned_to|find_user)_\d+/g, '$1_' + nextid);
		newrow_script.innerHTML = res;

		newrow_ac_input_id = newrow.querySelector('#find_user_' + nextid).id;
		newrow_ac_list_id = newrow.querySelector('#assigned_to_' + nextid + '_complete').id;

		table.append(newrow);

		showstuff('assigned_to_' + nextid + '_complete');
		new Ajax.Autocompleter(
			newrow_ac_input_id,
			newrow_ac_list_id,
			"<?php echo Filters::noXSS($baseurl); ?>js/callbacks/usersearch.php", null
		);

		// disable "add rows"
		if (table.childNodes.length > 50 ) {
			document.getElementById('addrow').disabled = true;
		}
	}
	function removeRow(elem)
	{
		var row = elem.parentNode.parentNode;
		var i = Array.from(elem.parentNode.parentNode.parentNode.children).indexOf(row);

		if(i == 0) {
			return false;
		}

		table.deleteRow(i);

		// disable "add rows"
		if (document.getElementById('table').childNodes.length <= 50 ) {
			document.getElementById('addrow').removeAttribute('disabled');
		}
	}
	function pasteMultiLines(elem, e)
	{
		if(e && e.clipboardData && e.clipboardData.getData) {
			var strs = e.clipboardData.getData("text/plain").trim().split("\n");
			var table = document.getElementById("table");
			var rows = table.querySelectorAll(':scope tr');
			var i = Array.from(elem.parentNode.parentNode.parentNode.children).indexOf(elem.parentNode.parentNode);
			var index = elem.id.replace(/\d+$/, '');
			var k = 0;
			var limit = i - 1 + strs.length;
			for(var j = i; j <= limit; j++) {
				var tds = rows[j];

				if (typeof tds !== "undefined") {
					tds.querySelector('#' + index + j).value = strs[k];
				} else {
					createRow((index == 'summary_' ? strs[k] : ''), (index == 'details_' ? strs[k] : ''));
				}

				k++;
			}
		}
	}
	function Apply()
	{
		var table = document.getElementById("table");
		var rows = table.getElementsByTagName("tr");
		var fields = rows[0].getElementsByTagName("td");
		for(var i = 1; i <= rows.length-1; i++)
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
		if(parent == rows[length-1])
			createRow('','');
		parent.nextElementSibling.getElementsByTagName("input")[1].focus();
		return false;
	}
</script>
</form>
