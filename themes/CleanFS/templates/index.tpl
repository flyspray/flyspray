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
  <button id="searchthisproject" type="submit"><?= eL('searchthisproject') ?></button>
  <input class="text" id="searchtext" name="string" type="text" size="20" placeholder=" "
   maxlength="100" value="<?php echo Filters::noXSS(Get::val('string')); ?>" accesskey="q"/>
  <input type="hidden" name="project" value="<?php echo Filters::noXSS(Get::num('project', $proj->id)); ?>"/>
  <input type="hidden" name="do" value="index"/>
  <button type="submit" name="export_list" value="1" id="exporttasklist" title="<?= eL('exporttasklist') ?>"><i class="fa fa-download"></i></button>
<style>
#sc2,#s_searchstate{display:none;}
#searchstateactions{color:#999;display:block;cursor:pointer;}
#s_searchstate:checked ~ #sc2 {display:block;}
#s_searchstate ~ label::before { content: "\25bc";}
#s_searchstate:checked ~ label::before { content: "\25b2";}
</style>
<input id="s_searchstate" type="checkbox" name="advancedsearch"<?php if(Req::val('advancedsearch')): ?> checked="checked"<?php endif; ?>/>
<label id="searchstateactions" for="s_searchstate"><?= eL('advanced') ?></label>
<div id="sc2" class="switchcontent">
<?php if (!$user->isAnon()): ?>
<fieldset>
  <div class="save_search"><label for="save_search" id="lblsaveas"><?= eL('saveas') ?></label>
   <input class="text" type="text" value="<?php echo Filters::noXSS(Get::val('search_name')); ?>" id="save_search" name="search_name" size="15"/> <button onclick="savesearch('<?php echo Filters::escapeqs($_SERVER['QUERY_STRING']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', '<?= eL('saving') ?>', '<?php echo Filters::noJsXSS($_SESSION['csrftoken']); ?>')" type="button"><?= eL('OK') ?></button>
  </div>
</fieldset>
<?php endif; ?>
<fieldset class="advsearch_misc">
   <legend><?= eL('miscellaneous') ?></legend>
   <?php echo tpl_checkbox('search_in_comments', Get::has('search_in_comments'), 'sic'); ?>
                    <label class="left" for="sic"><?= eL('searchcomments') ?></label>

                    <?php echo tpl_checkbox('search_in_details', Get::has('search_in_details'), 'search_in_details'); ?>
                    <label class="left" for="search_in_details"><?= eL('searchindetails') ?></label>

                    <?php echo tpl_checkbox('search_for_all', Get::has('search_for_all'), 'sfa'); ?>
                    <label class="left" for="sfa"><?= eL('searchforall') ?></label>

                    <?php echo tpl_checkbox('only_watched', Get::has('only_watched'), 'only_watched'); ?>
                    <label class="left" for="only_watched"><?= eL('taskswatched') ?></label>

                    <?php echo tpl_checkbox('only_primary', Get::has('only_primary'), 'only_primary'); ?>
                    <label class="left" for="only_primary"><?= eL('onlyprimary') ?></label>

		<?php echo tpl_checkbox('only_blocker', Get::has('only_blocker'), 'only_blocker'); ?>
		<label class="left" for="only_blocker" id="blockerlabel"><?= eL('onlyblocker') ?></label>
		<span id="blockerornoblocker"><?= eL('blockerornoblocker') ?></span>
		<style>
		#blockerornoblocker {display:none;color:#c00;}
		#only_primary:checked ~ #only_blocker:checked ~ #blockerornoblocker {display:inline;}
		</style>
		
                    <?php echo tpl_checkbox('has_attachment', Get::has('has_attachment'), 'has_attachment'); ?>
                    <label class="left" for="has_attachment"><?= eL('hasattachment') ?></label>

                    <?php echo tpl_checkbox('hide_subtasks', Get::has('hide_subtasks'), 'hide_subtasks'); ?>
                    <label class="left" for="hide_subtasks"><?= eL('hidesubtasks') ?></label>
                </fieldset>

                <fieldset class="advsearch_task">
                    <legend><?= eL('taskproperties') ?></legend>
            <!-- Task Type -->
		    <?php if (!$filter || in_array('tasktype', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="type"><?= eL('tasktype') ?></label>
                        <select name="type[]" id="type" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('alltasktypes')) + $proj->listTaskTypes(), isset($_GET['type'])?$_GET['type']:''); ?>
                        </select>
                    </div>

            <!-- Severity -->
		    <?php if (!$filter || in_array('severity', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="sev"><?= eL('severity') ?></label>
                        <select name="sev[]" id="sev" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('allseverities')) + $fs->severities, isset($_GET['sev'])?$_GET['sev']:''); ?>
                        </select>
                    </div>

            <!-- Priority -->
		    <?php if (!$filter || in_array('priority', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="pri"><?= eL('priority') ?></label>
                        <select name="pri[]" id="pri" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('allpriorities')) + $fs->priorities, isset($_GET['pri'])?$_GET['pri']:''); ?>
                        </select>
                    </div>

            <!-- Due Version -->
		    <?php if (!$filter || in_array('dueversion', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="due"><?= eL('dueversion') ?></label>
                        <select name="due[]" id="due" multiple="multiple" size="8">
                            <?php echo tpl_options(array_merge(array('' => L('dueanyversion'), 0 => L('unassigned')), $proj->listVersions(false)), isset($_GET['due'])?$_GET['due']:''); ?>
                        </select>
                    </div>

            <!-- Reportedin -->
		    <?php if (!$filter || in_array('reportedin', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="reported"><?= eL('reportedversion') ?></label>
                        <select name="reported[]" id="reported" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('anyversion')) + $proj->listVersions(false), isset($_GET['reported'])?$_GET['reported']:''); ?>
                        </select>
                    </div>

            <!-- Category -->
		    <?php if (!$filter || in_array('category', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="cat"><?= eL('category') ?></label>
                        <select name="cat[]" id="cat" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('allcategories')) + $proj->listCategories(), isset($_GET['cat'])?$_GET['cat']:''); ?>
                        </select>
                    </div>

            <!-- Status -->
		    <?php if (!$filter || in_array('status', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="status"><?= eL('status') ?></label>
                        <select name="status[]" id="status" multiple="multiple" size="8">
                            <?php echo tpl_options(array('' => L('allstatuses')) +
                            array('open' => L('allopentasks')) +
                            array('closed' => L('allclosedtasks')) +
                            $proj->listTaskStatuses(), isset($_GET['status'])?$_GET['status']:'open'); ?>
                        </select>
                    </div>

            <!-- Progress -->
		    <?php if (!$filter || in_array('progress', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="percent"><?= eL('percentcomplete') ?></label>
                        <!-- legacy: tpl_options()
                        <select name="percent[]" id="percent" multiple="multiple" size="12">
                            <?php $percentages = array(); for ($i = 0; $i <= 100; $i += 10) $percentages[$i] = $i; ?>
                            <?php echo tpl_options(array('' => L('anyprogress')) + $percentages, isset($_GET['percent'])?$_GET['percent']:''); ?>
                        </select>
                        -->
<?php
# new: use of tpl_select() which provides much more control
# maybe move some of the php code from here to scripts/index.php ...
$selected=isset($_GET['percent'])?$_GET['percent']:'';
$selected = is_array($selected) ? $selected : (array) $selected;
$percentages = array();
$percentages[]=array('value'=>'', 'label'=>L('anyprogress') );
if(in_array('', $selected, true)){
        $percentages[0]['attr']['selected']='selected';
}
for($i = 0; $i <= 100; $i += 10){
	$opt = array();
	$opt['value'] = $i;
	$opt['label'] = $i;
	# goes to theme.css ..
	# styling of html select options probably works only in a few browsers (at least firefox), but where it works it can be an added value.
	$opt['attr']=array('style'=>'background:linear-gradient(90deg,#0c0 0%,#0c0 '.$i.'%, #fff '.$i.'%, #fff 100%)');
	$opt['attr']=array('class'=>'percent'.$i);
	if(in_array("$i", $selected)){
		$opt['attr']['selected']='selected';
	}
	$percentages[]=$opt;
}
echo tpl_select(
	array(
		'name'=>'percent[]',
		'attr'=>array(
			'id'=>'percent',
			'multiple'=>'multiple',
			'size'=>12
		),
		'options'=>$percentages
	)
);
?>
                    </div>
                    <div class="clear"></div>
                </fieldset>

                <fieldset class="advsearch_users">
                    <legend><?= eL('users') ?></legend>
                    <label class="default multisel" for="opened"><?= eL('openedby') ?></label>
                    <?php echo tpl_userselect('opened', Get::val('opened'), 'opened', array('placeholder'=>' ')); ?>

		<?php if (!$filter || in_array('assignedto', $fields)) { ?>
                    <label class="default multisel" for="dev"><?= eL('assignedto') ?></label>
                    <?php echo tpl_userselect('dev', Get::val('dev'), 'dev', array('placeholder'=>' ')); } ?>
                    <label class="default multisel" for="closed"><?= eL('closedby') ?></label>
                    <?php echo tpl_userselect('closed', Get::val('closed'), 'closed', array('placeholder'=>' ')); ?>
                </fieldset>

                <fieldset class="advsearch_dates">
                    <legend><?= eL('dates') ?></legend>
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
<table id="tasklist_table">
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
	<th class="ttcolumn"><a title="<?= eL('toggleselected') ?>" href="javascript:ToggleSelected('massops')" onclick="massSelectBulkEditCheck();"></a></th>
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
	<td class="caret"></td>
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
        <legend><b><?= eL('updateselectedtasks') ?></b></legend>
        <ul class="form_elements slim">
            <input type="hidden" name="action" value="task.bulkupdate" />
            <input type="hidden" name="user_id" value="<?php echo Filters::noXSS($user->id); ?>"/>
            <!-- Quick Actions -->
            <li>
                <label for="bulk_quick_action"><?= eL('quickaction') ?></label>
                <select name="bulk_quick_action" id="bulk_quick_action">
                    <option value="0"><?= eL('notspecified') ?></option>
                    <option value="bulk_start_watching"><?= eL('watchtasks') ?></option>
                    <option value="bulk_stop_watching"><?= eL('stopwatchingtasks') ?></option>
                    <option value="bulk_take_ownership"><?= eL('assigntaskstome') ?></option>
                </select>
            </li>
            <!-- Status -->
            <?php if (in_array('status', $fields)) { ?>
            <li>
                <?php } else { ?>
            <li style="display:none">
                <?php } ?>

                <label for="bulk_status"><?= eL('status') ?></label>
                <select id="bulk_status" name="bulk_status">
                    <?php $statusList = $proj->listTaskStatuses(); ?>
                    <?php array_unshift($statusList,L('notspecified')); ?>
                    <?php echo tpl_options($statusList); ?>

                </select>
            </li>

            <!-- Progress -->
            <?php if (in_array('progress', $fields)) { ?>
            <li>
                <?php } else { ?>
            <li style="display:none">
                <?php } ?>
                <label for="bulk_percent"><?= eL('percentcomplete') ?></label>
                <select id="bulk_percent" name="bulk_percent_complete">
		<?php
			$percentCompleteList[] = array('', L('notspecified'));
			for ($i = 0; $i<=100; $i+=10) {
				$percentCompleteList[] = array($i, $i.'%');
			}
			echo tpl_options($percentCompleteList); 
		?>
                </select>
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
                <select id="bulk_tasktype" name="bulk_task_type">
                    <?php echo tpl_options($taskTypeList); ?>
                </select>

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
                <select id="bulk_category" name="bulk_category">
                    <?php echo tpl_options($categoryTypeList); ?>
                </select>

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
                <select size="8" style="height: 200px;" name="bulk_assignment[]" id="bulk_assignment" multiple>
                    <?php foreach ($userlist as $group => $users): ?>
                    <optgroup <?php if($group == '0'){ ?> label='<?= eL('pleaseselect') ?> ... ' <?php } else { ?> label='<?php echo Filters::noXSS($group); ?>' <?php } ?> >
                        <?php foreach ($users as $info): ?>
                        <option value="<?php echo Filters::noXSS($info[0]); ?>"><?php echo Filters::noXSS($info[1]); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
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
                <select id="bulk_os" name="bulk_os">
                    <?php echo tpl_options($osTypeList); ?>
                </select>
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
                <select id="bulk_severity" name="bulk_severity">
                    <?php echo tpl_options($severityTypeList); ?>
                </select>
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
                <select id="bulk_priority" name="bulk_priority">
                    <?php echo tpl_options($priorityTypeList); ?>
                </select>
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
                <select id="bulk_reportedver" name="bulk_reportedver">
                    <?php echo tpl_options($reportedVerList); ?>
                </select>
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
                <select id="bulk_dueversion" name="bulk_due_version">
                    <?php echo tpl_options($dueInVerList); ?>
                </select>
            </li>

            <!-- Due Date -->
            <?php if (in_array('duedate', $fields)) { ?>
            <li>
                <?php } else { ?>
            <li style="display:none">
                <?php } ?>
                <label for="bulk_due_date"><?= eL('duedate') ?></label>
                <?php echo tpl_datepicker('bulk_due_date'); ?>
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
                <select id="bulk_projects" name="bulk_projects">
                    <?php echo tpl_options($projectsList); ?>
                </select>
            </li>
        </ul>
        <button type="submit" name="updateselectedtasks" value="true"><?= eL('updateselectedtasks') ?></button>
    </fieldset>
    <fieldset>
	<legend><b><?php echo L('closeselectedtasks'); ?></b></legend>
            <div>
                <select class="adminlist" name="resolution_reason" onmouseup="event.stopPropagation();">
                    <option value="0"><?= eL('selectareason') ?></option>
                    <?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>
                </select>
                <button type="submit" name="updateselectedtasks" value="false"><?php echo L('closetasks'); ?></button>
                <br/>
                <label class="default text" for="closure_comment"><?= eL('closurecomment') ?></label>
                <textarea class="text" id="closure_comment" name="closure_comment" rows="3"
                          cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
                <label><?php echo tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close'))); ?>&nbsp;&nbsp;<?php echo Filters::noXSS(L('mark100')); ?></label>
            </div>
    </fieldset>

</div>
<?php endif; /* !$user->isAnon() && $proj-> !=0 && $total */ ?>
</div>
</form>
</div>
<?php endif; /* isset($_GET['string']) || $total */ ?>
