<script type="text/javascript">
	//Used for dynamically displaying the bulk edit pane, when Checkboxes are >1
	function BulkEditCheck()
	{
		var form = document.getElementById('massops');
		var count = 0;
		for(var n=0;n < form.length;n++){
			if(form[n].name == 'ids[]' && form[n].checked){
				count++;
			}
		}

		if(count == 0)
		{
			Effect.Fade('bulk_edit_selectedItems',{ duration: 0.2 });
		}
		if(count == 1)
		{
			Effect.Appear('bulk_edit_selectedItems',{ duration: 0.2 });
		}
	}

	function massSelectBulkEditCheck()
	{
		var form = document.getElementById('massops');
		var check_count = 0, uncheck_count;
		for(var n=0;n < form.length;n++){
			if(form[n].name == 'ids[]'){
		if(form[n].checked)
			check_count++;
		else
			uncheck_count++;
			}
		}

		if(check_count == 0)
		{
			Effect.Appear('bulk_edit_selectedItems',{ duration: 0.2 });
		}

		if(uncheck_count == 0)
		{
			Effect.Fade('bulk_edit_selectedItems',{ duration: 0.2 });
		}
	}

	function ClearAssignments()
	{
		document.getElementById('bulk_assignment').options.length = 0;
	}

	function exclusiveBlockerCheck(e)
	{
		var me = e.target;
		var other = $(me.id == 'only_primary' ? 'only_blocker' : 'only_primary');

		if(me.checked)
		{
			other.disable().checked = false;
		}
		else
		{
			other.enable();
		}
	}

	document.observe('dom:loaded', function()
	{
		$$('#only_primary, #only_blocker').each(function(s)
		{
			s.observe('click', exclusiveBlockerCheck)
		});
	});
</script>

<?php if(isset($update_error)): ?>
<div id="updatemsg">
	<span class="bad"> <?= eL('updatewrong') ?></span>
	<a href="?hideupdatemsg=yep"><?= eL('hidemessage') ?></a>
</div>
<?php endif; ?>

<?php if(isset($updatemsg)): ?>
<div id="updatemsg">
	<a href="http://flyspray.org/"><?= eL('updatefs') ?></a> <?= eL('currentversion') ?>

	<span class="bad"><?php echo Filters::noXSS($fs->version); ?></span> <?= eL('latestversion') ?> <span class="good"><?php echo Filters::noXSS($_SESSION['latest_version']); ?></span>.
	<a href="?hideupdatemsg=yep"><?= eL('hidemessage') ?></a>
</div>
<?php endif; ?>

<?php if (!($user->isAnon() &&  (count($fs->projects) == 0 || ($proj->id >0 && !$user->can_view_project($proj->id)))) ): ?>
<?php $filter = false; if($proj->id > 0) { $filter = true; $fields = explode( ' ', $proj->prefs['visible_fields'] );} ?>
<form id="search" action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
<div id="sc1">
	<button id="searchthisproject" type="submit"><span class="fas fa-magnifying-glass"></span> <?= eL('searchthisproject') ?></button>
	<input class="text fi-large" id="searchtext" name="string" type="text" size="20" placeholder=" "
	 maxlength="100" value="<?php echo Filters::noXSS(Get::val('string')); ?>" accesskey="q"/>
	<input type="hidden" name="project" value="<?php echo Filters::noXSS(Get::num('project', $proj->id)); ?>"/>
	<input type="hidden" name="do" value="index"/>
	<input id="s_searchstate" type="checkbox" name="advancedsearch"<?php if(Req::val('advancedsearch')): ?> checked="checked"<?php endif; ?>/>
	<label id="searchstateactions" class="button" for="s_searchstate"><?= eL('advanced') ?><span class="fas fa-caret-<?= (Req::val('advancedsearch') ? 'up' : 'down') ?> fa-lg"></span></label>
	<button type="submit" name="export_list" value="1" id="exporttasklist" title="<?= eL('exporttasklist') ?>"><span class="fas fa-download"></span> <?= eL('exporttasklist') ?></button>
</div>
<div id="sc2" class="switchcontent"<?php if(!Req::val('advancedsearch')): ?> style="display: none;"<?php endif; ?>>
<?php if (!$user->isAnon()): ?>
<fieldset>
	<div class="save_search">
		<label for="save_search" id="lblsaveas"><?= eL('saveas') ?></label>
		<input class="text fi-medium" type="text" value="<?php echo Filters::noXSS(Get::val('search_name')); ?>" id="save_search" name="search_name" size="15"/>
		<button onclick="savesearch('<?php echo Filters::escapeqs($_SERVER['QUERY_STRING']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', '<?= eL('saving') ?>', '<?php echo Filters::noJsXSS($_SESSION['csrftoken']); ?>')" type="button"><?= eL('OK') ?></button>
	</div>
</fieldset>
<?php endif; ?>
<fieldset class="advsearch_misc">
	<legend><?= eL('miscellaneous') ?></legend>
	<div class="searchfieldwrap">
		<div>
			<?php echo tpl_checkbox('search_in_comments', Get::has('search_in_comments'), 'sic'); ?>
			<label class="left" for="sic"><?= eL('searchcomments') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('search_in_details', Get::has('search_in_details'), 'search_in_details'); ?>
			<label class="left" for="search_in_details"><?= eL('searchindetails') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('search_for_all', Get::has('search_for_all'), 'sfa'); ?>
			<label class="left" for="sfa"><?= eL('searchforall') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('only_watched', Get::has('only_watched'), 'only_watched'); ?>
			<label class="left" for="only_watched"><?= eL('taskswatched') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('only_primary', Get::has('only_primary'), 'only_primary'); ?>
			<label class="left" for="only_primary"><?= eL('onlyprimary') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('only_blocker', Get::has('only_blocker'), 'only_blocker'); ?>
			<label class="left" for="only_blocker" id="blockerlabel"><?= eL('onlyblocker') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('has_attachment', Get::has('has_attachment'), 'has_attachment'); ?>
			<label class="left" for="has_attachment"><?= eL('hasattachment') ?></label>
		</div>
		<div>
			<?php echo tpl_checkbox('hide_subtasks', Get::has('hide_subtasks'), 'hide_subtasks'); ?>
			<label class="left" for="hide_subtasks"><?= eL('hidesubtasks') ?></label>
		</div>
	</div>
</fieldset>

<fieldset class="advsearch_task">
	<legend><?= eL('taskproperties') ?></legend>
	<div class="searchfieldwrap">
			<!-- Task Type -->
			<?php if (!$filter || in_array('tasktype', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('tasktype') ?></label>
						<div class="checksetwrap">
<?php
	$tasktype_opts = array('' => L('alltasktypes')) + $proj->listTaskTypes();
	$tasktype_vals = isset($_GET['type']) ? $_GET['type'] : [''];

	echo tpl_checkboxlist('type', $tasktype_opts, $tasktype_vals);
?>
						</div>
					</div>

			<!-- Severity -->
			<?php if (!$filter || in_array('severity', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('severity') ?></label>
						<div class="checksetwrap">
<?php
	$sev_opts = array('' => L('allseverities')) + $fs->severities;
	$sev_vals = isset($_GET['sev']) ? $_GET['sev'] : [''];

	echo tpl_checkboxlist('sev', $sev_opts, $sev_vals);
?>
						</div>
					</div>

			<!-- Priority -->
			<?php if (!$filter || in_array('priority', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('priority') ?></label>
						<div class="checksetwrap">
<?php
	$pri_opts = array('' => L('allpriorities')) + $fs->priorities;
	$pri_vals = isset($_GET['pri']) ? $_GET['pri'] : [''];

	echo tpl_checkboxlist('pri', $pri_opts, $pri_vals);
?>
						</div>
					</div>

			<!-- Due Version -->
			<?php if (!$filter || in_array('dueversion', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('dueversion') ?></label>
						<div class="checksetwrap">
<?php
	$dueversion_opts = array_merge(array('' => L('dueanyversion'), 0 => L('unassigned')), $proj->listVersions(false));
	$dueversion_vals = isset($_GET['due']) ? $_GET['due'] : [''];

	echo tpl_checkboxlist('due', $dueversion_opts, $dueversion_vals);
?>
						</div>
					</div>

			<!-- Reportedin -->
			<?php if (!$filter || in_array('reportedin', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('reportedversion') ?></label>
						<div class="checksetwrap">
<?php
	$reported_opts = array('' => L('anyversion')) + $proj->listVersions(false);
	$reported_vals = isset($_GET['reported']) ? $_GET['reported'] : [''];

	echo tpl_checkboxlist('due', $reported_opts, $reported_vals);
?>
						</div>
					</div>

			<!-- Category -->
			<?php if (!$filter || in_array('category', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('category') ?></label>
						<div class="checksetwrap">
<?php
	$cat_opts = array('' => L('allcategories')) + $proj->listCategories();
	$cat_vals = isset($_GET['cat']) ? $_GET['cat'] : [''];

	echo tpl_checkboxlist('cat', $cat_opts, $cat_vals);
?>
						</div>
					</div>

			<!-- Status -->
			<?php if (!$filter || in_array('status', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel"><?= eL('status') ?></label>
						<div class="checksetwrap">
<?php
	$status_opts = array(
		'' => L('allstatuses')) +
		array('open' => L('allopentasks')) +
		array('closed' => L('allclosedtasks')) +
		$proj->listTaskStatuses();
	$status_vals = isset($_GET['status']) ? $_GET['status'] : ['open'];

	echo tpl_checkboxlist('status', $status_opts, $status_vals);
?>
						</div>
					</div>

			<!-- Progress -->
			<?php if (!$filter || in_array('progress', $fields)) { ?>
					<div class="search_select">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<label class="multisel" for="percent"><?= eL('percentcomplete') ?></label>
						<div class="checksetwrap">
<?php
$selected = isset($_GET['percent']) ? $_GET['percent'] : [''];
$percentages = array();
$percentages[''] = L('anyprogress');

for($i = 0; $i <= 100; $i += 10){
	$percentages[] = [0 => $i, 1 => $i . '%'];
/*
	$opt = array();
	$opt['value'] = $i;
	$opt['label'] = $i;
	$opt['attr'] = array(
		'style'=>'background:linear-gradient(90deg,#acdc7d 0%,#78bf34 '.$i.'%, transparent '.$i.'%, transparent 100%)',
		'class'=>'percent'.$i
	);
	$percentages[] = $opt;
*/
}

echo tpl_checkboxlist('percent', $percentages, $selected);
?>
						</div>
					</div>

					</div>
				</fieldset>

				<fieldset class="advsearch_users">
					<legend><?= eL('users') ?></legend>
					<div class="searchfieldwrap">
					<div>
					<label class="default multisel" for="opened"><?= eL('openedby') ?></label>
					<?php echo tpl_userselect('opened', Get::val('opened'), 'opened', array('placeholder'=>' ')); ?>
					</div>
		<?php if (!$filter || in_array('assignedto', $fields)) { ?>
					<div>
					<label class="default multisel" for="dev"><?= eL('assignedto') ?></label>
					<?php echo tpl_userselect('dev', Get::val('dev'), 'dev', array('placeholder'=>' ')); } ?>
					</div>
					<div>
					<label class="default multisel" for="closed"><?= eL('closedby') ?></label>
					<?php echo tpl_userselect('closed', Get::val('closed'), 'closed', array('placeholder'=>' ')); ?>
					</div>
					</div>
				</fieldset>

				<fieldset class="advsearch_dates">
					<legend><?= eL('dates') ?></legend>
					<div class="searchfieldwrap">
			<!-- Due Date -->
			<?php if (!$filter || in_array('duedate', $fields)) { ?>
					<div class="dateselect">
			<?php } else { ?>
			<div style="display:none">
			<?php } ?>
						<?php echo tpl_datepicker('duedatefrom', L('selectduedatefrom')); ?>
						<?php echo tpl_datepicker('duedateto', L('selectduedateto')); ?>
					</div>
					<div class="dateselect">
						<?php echo tpl_datepicker('changedfrom', L('selectsincedatefrom')); ?>
						<?php echo tpl_datepicker('changedto', L('selectsincedateto')); ?>
					</div>
					<div class="dateselect">
						<?php echo tpl_datepicker('openedfrom', L('selectopenedfrom')); ?>
						<?php echo tpl_datepicker('openedto', L('selectopenedto')); ?>
					</div>
					<div class="dateselect">
						<?php echo tpl_datepicker('closedfrom', L('selectclosedfrom')); ?>
						<?php echo tpl_datepicker('closedto', L('selectclosedto')); ?>
					</div>
					</div>
				</fieldset>
			</div>
</form>
<?php endif; ?>
<?php if (isset($_GET['string']) || $total): ?>
<div id="tasklist">
<?php echo tpl_form(Filters::noXSS(createURL('project', $proj->id, null, $_GET)),'massops',null,null,'id="massops"'); ?>
<div>
<script type="text/javascript">
	var cX = 0; var cY = 0; var rX = 0; var rY = 0;
	function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
	function UpdateCursorPositionDocAll(e){ cX = e.clientX; cY = e.clientY;}
	if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
	else { document.onmousemove = UpdateCursorPosition; }
	function AssignPosition(d) {
		if (self.pageYOffset) {
			rX = self.pageXOffset;
			rY = self.pageYOffset;
		} else if(document.documentElement && document.documentElement.scrollTop) {
			rX = document.documentElement.scrollLeft;
			rY = document.documentElement.scrollTop;
		} else if(document.body) {
			rX = document.body.scrollLeft;
			rY = document.body.scrollTop;
		}
		if (document.all) {
			cX += rX;
			cY += rY;
		}
		d.style.left = (cX+10) + "px";
		d.style.top = (cY+10) + "px";
	}
	function Show(elem, id) {
		if(cY == 0) return;
		var div = document.getElementById("desc_"+id);
		AssignPosition(div);
		div.style.display = "block";
	}
	function Hide(elem, id)	{
		document.getElementById("desc_"+id).style.display = "none";
	}
</script>
<table id="tasklist_table" class="tasklist">
<colgroup>
	<col class="caret" />
	<?php if (!$user->isAnon() && $proj->id !=0 && $total): ?><col class="toggle" /><?php endif; ?>
	<?php foreach ($visible as $col): ?>
	<col class="<?php echo $col; ?>" />
	<?php endforeach; ?>
</colgroup>
<thead>
<tr>
	<th class="caret"></th>
	<?php if (!$user->isAnon() && $proj->id !=0 && $total): ?>
	<th class="ttcolumn"><a title="<?= eL('toggleselected') ?>" href="javascript:ToggleSelected('massops')" onclick="massSelectBulkEditCheck();"><span class="fas fa-exchange"></span></a></th>
	<?php
	endif;
	foreach ($visible as $col):
	echo tpl_list_heading($col, "\n\t<th%s>%s</th>");
	endforeach;
	?>
</tr>
</thead>
<tbody>
<?php
# to limit preloading detailed_desc in tasklist table in favor of smaller page size and time spend by TextFormatter::render()
$maxrender=25;
$taskcount=0;
foreach ($tasks as $task): ?>
<tr id="task<?php echo $task['task_id']; ?>" class="severity<?php echo $task['task_severity'];  echo $task['is_closed'] ==1 ? ' closed': '';?>">
	<td class="caret"><span class="fas"></span></td>
	<?php if (!$user->isAnon() && $proj->id !=0): ?>
	<td class="ttcolumn"><input class="ticktask" type="checkbox" name="ids[]" onclick="BulkEditCheck()" value="<?php echo $task['task_id']; ?>"/></td>
	<?php
	endif;
	$taskcount++;
	foreach ($visible as $col):
		if($col == 'progress'):?>
	<td class="task_progress"><div class="progress_bar_container"><span><?php echo $task['percent_complete']; ?>%</span><div class="progress_bar" style="width:<?php echo $task['percent_complete']; ?>%"></div></div></td>
		<?php elseif ($col == 'summary'):
			$sumtpl='<td class="%s"';
			if ($taskcount <= $maxrender) {
				# TODO: get rid of that inline javascript to make more strict content-security-policy possible.
				# Maybe replace by event listener that takes care of first $maxrender items?
				$sumtpl.=' onmouseover="Show(this, '.$task['task_id'].')" onmouseout="Hide(this, '.$task['task_id'].')"';
			}
			$sumtpl.='>%s</td>';
			echo tpl_draw_cell($task, $col, $sumtpl);
		else:
			echo tpl_draw_cell($task, $col);
		endif;
	endforeach;
	if ($taskcount <= $maxrender): ?>
	<td id="desc_<?php echo $task['task_id']; ?>" class="descbox box">
	<b><?php echo L('taskdescription'); ?></b>
	<?php echo $task['detailed_desc'] ? TextFormatter::render($task['detailed_desc'], 'task', $task['task_id'], $task['desccache']) : '<p>'.L('notaskdescription').'</p>'; ?>
	</td>
	<?php else: ?>
	<td></td>
	<?php endif;?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if ($total): ?>
	<span class="taskrange"><?php echo sprintf(L('taskrange'), $offset + 1, ($offset + $perpage > $total ? $total : $offset + $perpage), $total); ?></span>
	<?php echo pagenums($pagenum, $perpage, $total); ?>
<?php else: ?>
	<div class="noresult"><strong><?= eL('noresults') ?></strong></div>
<?php endif; ?>

<!-- Bulk editing Tasks -->
<?php if (!$user->isAnon() && $proj->id !=0 && $total): ?>
<!-- Grab fields wanted for this project so we only show those specified in the settings -->
<div id="bulk_edit_selectedItems" style="display:none">
	<fieldset>
		<legend><?= eL('updateselectedtasks') ?></legend>
		<ul class="form_elements">
			<input type="hidden" name="action" value="task.bulkupdate" />
			<input type="hidden" name="user_id" value="<?php echo Filters::noXSS($user->id); ?>"/>
			<!-- Quick Actions -->
			<li>
				<label for="bulk_quick_action"><?= eL('quickaction') ?></label>
				<div class="valuewrap">
					<select name="bulk_quick_action" id="bulk_quick_action">
						<option value="0"><?= eL('notspecified') ?></option>
						<option value="bulk_start_watching"><?= eL('watchtasks') ?></option>
						<option value="bulk_stop_watching"><?= eL('stopwatchingtasks') ?></option>
						<option value="bulk_take_ownership"><?= eL('assigntaskstome') ?></option>
					</select>
				</div>
			</li>
			<!-- Status -->
			<?php if (in_array('status', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>

				<label for="bulk_status"><?= eL('status') ?></label>
				<div class="valuewrap">
					<select id="bulk_status" name="bulk_status">
						<?php $statusList = $proj->listTaskStatuses(); ?>
						<?php array_unshift($statusList,L('notspecified')); ?>
						<?php echo tpl_options($statusList); ?>
					</select>
				</div>
			</li>

			<!-- Progress -->
			<?php if (in_array('progress', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<label for="bulk_percent"><?= eL('percentcomplete') ?></label>
				<div class="valuewrap">
					<select id="bulk_percent" name="bulk_percent_complete">
		<?php
			$percentCompleteList[] = array('', L('notspecified'));
			for ($i = 0; $i<=100; $i+=10) {
				$percentCompleteList[] = array($i, $i.'%');
			}
			echo tpl_options($percentCompleteList);
		?>
					</select>
				</div>
			</li>

			<!-- Task Type -->
			<?php if (in_array('tasktype', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $taskTypeList = $proj->listTaskTypes(); ?>
				<?php array_unshift($taskTypeList,L('notspecified')); ?>
				<label for="bulk_tasktype"><?= eL('tasktype') ?></label>
				<div class="valuewrap">
					<select id="bulk_tasktype" name="bulk_task_type">
						<?php echo tpl_options($taskTypeList); ?>
					</select>
				</div>
			</li>

			<!-- Category -->
			<?php if (in_array('category', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $categoryTypeList = $proj->listCategories(); ?>
				<?php array_unshift($categoryTypeList,L('notspecified')); ?>
				<label for="bulk_category"><?php echo Filters::noXSS(L('category')); ?></label>
				<div class="valuewrap">
					<select id="bulk_category" name="bulk_category">
						<?php echo tpl_options($categoryTypeList); ?>
					</select>
				</div>
			</li>

			<!-- Assigned To-->
			<li>
				<?php if ($user->perms('edit_assignments')): ?>
				<label for="bulk_assignment"><?= eL('assignedto') ?></label>
				<?php
						//insert a noone into the list in order to bulk de-assign tasks
						$noone[0]=array(0,L('noone'));
						array_unshift($userlist, $noone);
				?>
				<div class="valuewrap">
					<select size="8" style="height: 200px;" name="bulk_assignment[]" id="bulk_assignment" multiple>
						<?php foreach ($userlist as $group => $users): ?>
						<optgroup <?php if($group == '0'){ ?> label='<?= eL('pleaseselect') ?> ... ' <?php } else { ?> label='<?php echo Filters::noXSS($group); ?>' <?php } ?> >
							<?php foreach ($users as $info): ?>
							<option value="<?php echo Filters::noXSS($info[0]); ?>"><?php echo Filters::noXSS($info[1]); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>
			</li>

			<!-- OS -->
			<?php if (in_array('os', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $osTypeList = $proj->listOs(); ?>
				<?php array_unshift($osTypeList,L('notspecified')); ?>
				<label for="bulk_os"><?= eL('operatingsystem') ?></label>
				<div class="valuewrap">
					<select id="bulk_os" name="bulk_os">
						<?php echo tpl_options($osTypeList); ?>
					</select>
				</div>
			</li>

			<!-- Severity -->
			<?php if (in_array('severity', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $severityTypeList = array_reverse($fs->severities); ?>
				<?php array_unshift($severityTypeList,L('notspecified')); ?>
				<label for="bulk_severity"><?= eL('severity') ?></label>
				<div class="valuewrap">
					<select id="bulk_severity" name="bulk_severity">
						<?php echo tpl_options($severityTypeList); ?>
					</select>
				</div>
			</li>

			<!-- Priority -->
			<?php if (in_array('priority', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>

				<?php $priorityTypeList = array_reverse($fs->priorities); ?>
				<?php array_unshift($priorityTypeList,L('notspecified')); ?>
				<label for="bulk_priority"><?= eL('priority') ?></label>
				<div class="valuewrap">
					<select id="bulk_priority" name="bulk_priority">
						<?php echo tpl_options($priorityTypeList); ?>
					</select>
				</div>
			</li>

			<!-- Reported In -->
			<?php if (in_array('reportedin', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $reportedVerList = $proj->listVersions(); ?>
				<?php array_unshift($reportedVerList,L('notspecified')); ?>
				<label for="bulk_reportedver"><?= eL('reportedversion') ?></label>
				<div class="valuewrap">
					<select id="bulk_reportedver" name="bulk_reportedver">
						<?php echo tpl_options($reportedVerList); ?>
					</select>
				</div>
			</li>

			<!-- Due -->
			<?php if (in_array('dueversion', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<?php $dueInVerList = $proj->listVersions(); ?>
				<?php array_unshift($dueInVerList,L('undecided')); ?>
				<?php array_unshift($dueInVerList,L('notspecified')); ?>
				<label for="bulk_dueversion"><?= eL('dueinversion') ?></label>
				<div class="valuewrap">
					<select id="bulk_dueversion" name="bulk_due_version">
						<?php echo tpl_options($dueInVerList); ?>
					</select>
				</div>
			</li>

			<!-- Due Date -->
			<?php if (in_array('duedate', $fields)) { ?>
			<li>
				<?php } else { ?>
			<li style="display:none">
				<?php } ?>
				<label for="bulk_due_date"><?= eL('duedate') ?></label>
				<div class="valuewrap">
				<?php echo tpl_datepicker('bulk_due_date'); ?>
				</div>
			</li>

			<!-- Projects -->
			<!-- If there is only one choice of project, then don't bother showing it -->
			<?php if (count($fs->projects) > 1) { ?>
			<li>
			<?php } else { ?>
			<li style="display:none">
			<?php } ?>
				<?php $projectsList = $fs->listProjects(); ?>
				<?php array_unshift($projectsList,L('notspecified')); ?>
				<label for="bulk_projects"><?= eL('attachedtoproject') ?></label>
				<div class="valuewrap">
					<select id="bulk_projects" name="bulk_projects">
						<?php echo tpl_options($projectsList); ?>
					</select>
				</div>
			</li>
		</ul>
		<button type="submit" name="updateselectedtasks" value="true"><?= eL('updateselectedtasks') ?></button>
	</fieldset>
	<fieldset>
	<legend><?php echo L('closeselectedtasks'); ?></legend>
			<div>
				<div>
				<select class="adminlist" name="resolution_reason" onmouseup="event.stopPropagation();">
					<option value="0"><?= eL('selectareason') ?></option>
					<?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>
				</select>
				<button type="submit" name="updateselectedtasks" value="false"><?php echo L('closetasks'); ?></button>
				</div>
				<div>
				<label class="default text" for="closure_comment"><?= eL('closurecomment') ?></label>
				<textarea class="text txta-medium" id="closure_comment" name="closure_comment" rows="3"
						  cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
				</div>
				<div>
				<label><?php echo tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close'))); ?>&nbsp;&nbsp;<?php echo Filters::noXSS(L('mark100')); ?></label>
				</div>
			</div>
	</fieldset>

</div>
<?php endif; /* !$user->isAnon() && $proj-> !=0 && $total */ ?>
</div>
</form>
</div>
<?php endif; /* isset($_GET['string']) || $total */ ?>
