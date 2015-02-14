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
    <span class="bad"> <?php echo Filters::noXSS(L('updatewrong')); ?></span>
    <a href="?hideupdatemsg=yep"><?php echo Filters::noXSS(L('hidemessage')); ?></a>
</div>
<?php endif; ?>

<?php if(isset($updatemsg)): ?>
<div id="updatemsg">
    <a href="http://flyspray.org/"><?php echo Filters::noXSS(L('updatefs')); ?></a> <?php echo Filters::noXSS(L('currentversion')); ?>

    <span class="bad"><?php echo Filters::noXSS($fs->version); ?></span> <?php echo Filters::noXSS(L('latestversion')); ?> <span class="good"><?php echo Filters::noXSS($_SESSION['latest_version']); ?></span>.
    <a href="?hideupdatemsg=yep"><?php echo Filters::noXSS(L('hidemessage')); ?></a>
</div>
<?php endif; ?>

<?php if (!($user->isAnon() && count($fs->projects) == 0)): ?>
<?php $filter = false; if($proj->id > 0) { $filter = true; $fields = explode( ' ', $proj->prefs['visible_fields'] );} ?>
<div id="search">
    <map id="projectsearchform" name="projectsearchform">
        <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
            <div>
                <button id="searchthisproject" type="submit"><?php echo Filters::noXSS(L('searchthisproject')); ?></button>
                <input class="text" id="searchtext" name="string" type="text" size="20"
                       maxlength="100" value="<?php echo Filters::noXSS(Get::val('string')); ?>" accesskey="q"/>

                <input type="hidden" name="project" value="<?php echo Filters::noXSS(Get::num('project', $proj->id)); ?>"/>

        <span id="searchstate" style="cursor:pointer">
	        <a onclick="toggleSearchBox('<?php echo Filters::noJsXSS($this->themeUrl()); ?>');return false;"
               href="<?php echo Filters::noXSS(CreateUrl('project', $proj->id, null, array_merge($_GET, array('toggleadvanced' => 1)))); ?>">
						<span id="advancedsearchstate" class="showstate">
							<img id="advancedsearchstateimg"
                                 src="<?php echo (Cookie::val('advancedsearch')) ? $this->get_image('edit_remove') : $this->get_image('edit_add'); ?>"
                                 alt="<?php echo (Cookie::val('advancedsearch')) ? '-' : '+'; ?>"/>
						</span><?php echo Filters::noXSS(L('advanced')); ?>

            </a>
        </span>

                <div id="sc2" class="switchcontent"
                <?php if (!Cookie::val('advancedsearch')):?>style="display:none;"<?php endif; ?> >

                <?php if (!$user->isAnon()): ?>
                <fieldset>
                    <div class="save_search"><label for="save_search" id="lblsaveas"><?php echo Filters::noXSS(L('saveas')); ?></label>
                        <input class="text" type="text" value="<?php echo Filters::noXSS(Get::val('search_name')); ?>" id="save_search"
                               name="search_name" size="15"/>
                        &nbsp;
                        <button onclick="savesearch('<?php echo Filters::escapeqs($_SERVER['QUERY_STRING']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', '<?php echo Filters::noXSS(L('saving')); ?>')"
                                type="button"><?php echo Filters::noXSS(L('OK')); ?></button>
                    </div>
                </fieldset>
                <?php endif; ?>


                <fieldset class="advsearch_misc">
                    <legend><?php echo Filters::noXSS(L('miscellaneous')); ?></legend>
                    <?php echo tpl_checkbox('search_in_comments', Get::has('search_in_comments'), 'sic'); ?>

                    <label class="left" for="sic"><?php echo Filters::noXSS(L('searchcomments')); ?></label>

                    <?php echo tpl_checkbox('search_in_details', Get::has('search_in_details'), 'search_in_details'); ?>

                    <label class="left" for="search_in_details"><?php echo Filters::noXSS(L('searchindetails')); ?></label>

                    <?php echo tpl_checkbox('search_for_all', Get::has('search_for_all'), 'sfa'); ?>

                    <label class="left" for="sfa"><?php echo Filters::noXSS(L('searchforall')); ?></label>

                    <?php echo tpl_checkbox('only_watched', Get::has('only_watched'), 'only_watched'); ?>

                    <label class="left" for="only_watched"><?php echo Filters::noXSS(L('taskswatched')); ?></label>

                    <?php echo tpl_checkbox('only_primary', Get::has('only_primary'), 'only_primary'); ?>

                    <label class="left" for="only_primary"><?php echo Filters::noXSS(L('onlyprimary')); ?></label>

                    <?php echo tpl_checkbox('has_attachment', Get::has('has_attachment'), 'has_attachment'); ?>

                    <label class="left" for="has_attachment"><?php echo Filters::noXSS(L('hasattachment')); ?></label>

                    <?php echo tpl_checkbox('hide_subtasks', Get::has('hide_subtasks'), 'hide_subtasks'); ?>

                    <label class="left" for="hide_subtasks"><?php echo Filters::noXSS(L('hidesubtasks')); ?></label>
                </fieldset>

                <fieldset class="advsearch_task">

                    <legend><?php echo Filters::noXSS(L('taskproperties')); ?></legend>
            <!-- Task Type -->
		    <?php if (!$filter || in_array('tasktype', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="type"><?php echo Filters::noXSS(L('tasktype')); ?></label>
                        <select name="type[]" id="type" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('alltasktypes')) + $proj->listTaskTypes(), Get::val('type', '')); ?>

                        </select>
                    </div>

            <!-- Severity -->
		    <?php if (!$filter || in_array('severity', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="sev"><?php echo Filters::noXSS(L('severity')); ?></label>
                        <select name="sev[]" id="sev" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('allseverities')) + $fs->severities, Get::val('sev', '')); ?>

                        </select>
                    </div>

            <!-- Priority -->
		    <?php if (!$filter || in_array('priority', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="pri"><?php echo Filters::noXSS(L('priority')); ?></label>
                        <select name="pri[]" id="pri" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('allpriorities')) + $fs->priorities, Get::val('pri', '')); ?>

                        </select>
                    </div>

            <!-- Due Version -->
		    <?php if (!$filter || in_array('dueversion', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="due"><?php echo Filters::noXSS(L('dueversion')); ?></label>
                        <select name="due[]" id="due" multiple="multiple" size="5">
                            <?php echo tpl_options(array_merge(array('' => L('dueanyversion'), 0 => L('unassigned')), $proj->listVersions(false)), Get::val('due', '')); ?>

                        </select>
                    </div>

            <!-- Reportedin -->
		    <?php if (!$filter || in_array('reportedin', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="reported"><?php echo Filters::noXSS(L('reportedversion')); ?></label>
                        <select name="reported[]" id="reported" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('anyversion')) + $proj->listVersions(false), Get::val('reported', '')); ?>

                        </select>
                    </div>

            <!-- Category -->
		    <?php if (!$filter || in_array('category', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="cat"><?php echo Filters::noXSS(L('category')); ?></label>
                        <select name="cat[]" id="cat" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('allcategories')) + $proj->listCategories(), Get::val('cat', '')); ?>

                        </select>
                    </div>

            <!-- Status -->
		    <?php if (!$filter || in_array('status', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="status"><?php echo Filters::noXSS(L('status')); ?></label>
                        <select name="status[]" id="status" multiple="multiple" size="5">
                            <?php echo tpl_options(array('' => L('allstatuses')) +
                            array('open' => L('allopentasks')) +
                            array('closed' => L('allclosedtasks')) +
                            $proj->listTaskStatuses(), Get::val('status', 'open')); ?>

                        </select>
                    </div>

            <!-- Progress -->
		    <?php if (!$filter || in_array('progress', $fields)) { ?>
                    <div class="search_select">
		    <?php } else { ?>
		    <div style="display:none">
		    <?php } ?>
                        <label class="default multisel" for="percent"><?php echo Filters::noXSS(L('percentcomplete')); ?></label>
                        <select name="percent[]" id="percent" multiple="multiple" size="5">
                            <?php $percentages = array(); for ($i = 0; $i <= 100; $i += 10) $percentages[$i] = $i; ?>
                            <?php echo tpl_options(array('' => L('anyprogress')) + $percentages, Get::val('percent', '')); ?>

                        </select>
                    </div>
                    <div class="clear"></div>
                </fieldset>

                <fieldset class="advsearch_users">
                    <legend><?php echo Filters::noXSS(L('users')); ?></legend>
                    <label class="default multisel" for="opened"><?php echo Filters::noXSS(L('openedby')); ?></label>
                    <?php echo tpl_userselect('opened', Get::val('opened'), 'opened'); ?>

		    <?php if (!$filter || in_array('assignedto', $fields)) { ?>
                    <label class="default multisel" for="dev"><?php echo Filters::noXSS(L('assignedto')); ?></label>
                    <?php echo tpl_userselect('dev', Get::val('dev'), 'dev'); } ?>


                    <label class="default multisel" for="closed"><?php echo Filters::noXSS(L('closedby')); ?></label>
                    <?php echo tpl_userselect('closed', Get::val('closed'), 'closed'); ?>

                </fieldset>

                <fieldset class="advsearch_dates">
                    <legend><?php echo Filters::noXSS(L('dates')); ?></legend>
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
            <input type="hidden" name="do" value="index"/>

 <!--- Added 2/1/2014 LAE --!>

 <form action="<?php echo Filters::noXSS($baseurl); ?>/scripts/index.php" method="post">
 <input type='submit' name='export_list' value='<?php echo Filters::noXSS(L('exporttasklist')); ?>'>
 </form>

</div>

</form>
</map>
</div>
<?php endif; ?>

<div id="tasklist">
<form action="<?php echo Filters::noXSS(CreateURL('project', $proj->id, null, $_GET)); ?>" name="massops" id="massops" method="post">
<div>
<table id="tasklist_table">
    <thead>
    <tr>
        <th class="caret">
        </th>
        <?php if (!$user->isAnon()): ?>
        <th class="ttcolumn">
            <?php if (!$user->isAnon() && $total): ?>
            <a title="<?php echo Filters::noXSS(L('toggleselected')); ?>" href="javascript:ToggleSelected('massops')" onclick="massSelectBulkEditCheck();">
            </a>
            <?php endif; ?>
        </th>
        <?php endif; ?>
        <?php foreach ($visible as $col): ?>
        <?php echo tpl_list_heading($col, "<th%s>%s</th>"); ?>

        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <script type="text/javascript">
	var cX = 0; var cY = 0; var rX = 0; var rY = 0;
	function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
	function UpdateCursorPositionDocAll(e){ cX = e.clientX; cY = e.clientY;}
	if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
	else { document.onmousemove = UpdateCursorPosition; }
	function AssignPosition(d) {
		if(self.pageYOffset)
		{
			rX = self.pageXOffset;
			rY = self.pageYOffset;
		}
		else if(document.documentElement && document.documentElement.scrollTop) {
			rX = document.documentElement.scrollLeft;
			rY = document.documentElement.scrollTop;
		}
		else if(document.body) {
			rX = document.body.scrollLeft;
			rY = document.body.scrollTop;
		}
		if(document.all) {
			cX += rX; 
			cY += rY;
		}
		d.style.left = (cX+10) + "px";
		d.style.top = (cY+10) + "px";
	}
	function Show(elem, id)
	{
		var div = document.getElementById("desc_"+id);
		AssignPosition(div);
		div.style.display = "block";
	}
	function Hide(elem, id)
	{
		document.getElementById("desc_"+id).style.display = "none";
	}
    </script>
    <?php foreach ($tasks as $task_details):?>
    <tr id="task<?php echo $task_details['task_id']; ?>" class="severity<?php echo Filters::noXSS($task_details['task_severity']); ?>" onmouseover="Show(this,<?php echo $task_details['task_id']; ?>)" onmouseout="Hide(this, <?php echo $task_details['task_id']; ?>)">
        <td class="caret">
        </td>
        <?php if (!$user->isAnon()): ?>
        <td class="ttcolumn">
            <input class="ticktask" type="checkbox" name="ids[]" onclick="BulkEditCheck()" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
        </td>
        <?php endif;?>

        <?php foreach ($visible as $col):
						if($col == 'progress'):?>
        <td class="task_progress">
            <div class="progress_bar_container">
                <span><?php echo Filters::noXSS($task_details['percent_complete']); ?>%</span>

                <div class="progress_bar" style="width:<?php echo Filters::noXSS($task_details['percent_complete']); ?>%"></div>
            </div>
        </td>
        <?php else: ?>
        <?php echo tpl_draw_cell($task_details, $col); ?>

        <?php endif; endforeach; ?>
<div id="desc_<?php echo $task_details['task_id']; ?>" class="descbox box">
<b>Task Description:</b>
<?php echo $task_details['detailed_desc'] ? $task_details['detailed_desc'] : "<p>No Description</p>"; ?>
</div>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<table id="pagenumbers">
    <tr>
        <?php if ($total): ?>
        <td id="taskrange">
            <?php echo sprintf(L('taskrange'), $offset + 1,
            ($offset + $perpage > $total ? $total : $offset + $perpage), $total); ?>

            <?php if (!$proj->id == 0 && !$user->isAnon() && $total){ ?>
            <?php } ?>
        </td>
        <td id="numbers">
            <?php echo pagenums($pagenum, $perpage, $total); ?>

        </td>
        <?php else: ?>
        <td id="taskrange"><strong><?php echo Filters::noXSS(L('noresults')); ?></strong></td>
        <?php endif; ?>
    </tr>
</table>



<!--- Bulk editing Tasks --->
<?php if (!$proj->id == 0): ?>
<?php if (!$user->isAnon() && $total): ?>
<!-- Grab fields wanted for this project so we only show those specified in the settings -->
<script>Effect.Fade('bulk_edit_selectedItems');</script>
<div id="bulk_edit_selectedItems" style="display:none">
    <fieldset>
        <legend><b><?php echo Filters::noXSS(L('updateselectedtasks')); ?></b></legend>
        <ul class="form_elements slim">
            <input type="hidden" name="action" value="task.bulkupdate" />
            <input type="hidden" name="user_id" value="<?php echo Filters::noXSS($user->id); ?>"/>
            <!-- Quick Actions -->
            <li>
                <label for="bulk_quick_action"><?php echo Filters::noXSS(L('quickaction')); ?></label>
                <select name="bulk_quick_action" id="bulk_quick_action">
                    <option value="0"><?php echo Filters::noXSS(L('notspecified')); ?></option>
                    <option value="bulk_start_watching"><?php echo Filters::noXSS(L('watchtasks')); ?></option>
                    <option value="bulk_stop_watching"><?php echo Filters::noXSS(L('stopwatchingtasks')); ?></option>
                    <option value="bulk_take_ownership"><?php echo Filters::noXSS(L('assigntaskstome')); ?></option>
                </select>
            </li>
            <!-- Status -->
            <?php if (in_array('status', $fields)) { ?>
            <li>
                <?php } else { ?>
            <li style="display:none">
                <?php } ?>

                <label for="bulk_status"><?php echo Filters::noXSS(L('status')); ?></label>
                <select id="bulk_status" name="bulk_status">
                    <?php $statusList = $proj->ListTaskStatuses(); ?>
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
                <label for="bulk_percent"><?php echo Filters::noXSS(L('percentcomplete')); ?></label>
                <select id="bulk_percent" name="bulk_percent_complete">
                    <?php $percentCompleteList = array();$percentCompleteList[0]=L('notspecified'); for ($i = 1; $i<=101; $i+=10) $percentCompleteList[$i-1] =''.($i-1).'%'; ?>
                    <?php echo tpl_options($percentCompleteList); ?>

                </select>
            </li>

            <!-- Task Type-->
            <?php if (in_array('tasktype', $fields)) { ?>
            <li>
                <?php } else { ?>
            <li style="display:none">
                <?php } ?>
                <?php $taskTypeList = $proj->listTaskTypes(); ?>
                <?php array_unshift($taskTypeList,L('notspecified')); ?>
                <label for="bulk_tasktype"><?php echo Filters::noXSS(L('tasktype')); ?></label>
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
                <label for="bulk_assignment"><?php echo Filters::noXSS(L('assignedto')); ?></label>
                <?php
                        //insert a noone into the list in order to bulk de-assign tasks
                        $noone[0]=array(0,L('noone'));
                        array_unshift($userlist,$noone);
                ?>
                <select size="8" style="height: 200px;" name="bulk_assignment[]" id="bulk_assignment" multiple>
                    <?php foreach ($userlist as $group => $users): ?>
                    <optgroup <?php if($group == '0'){ ?> label='Please Select... ' <?php } else { ?> label='<?php echo Filters::noXSS($group); ?>' <?php } ?> >
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
                <label for="bulk_os"><?php echo Filters::noXSS(L('operatingsystem')); ?></label>
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
                <label for="bulk_severity"><?php echo Filters::noXSS(L('severity')); ?></label>
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
                <label for="bulk_priority"><?php echo Filters::noXSS(L('priority')); ?></label>
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
                <label for="bulk_reportedver"><?php echo Filters::noXSS(L('reportedversion')); ?></label>
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
                <label for="bulk_dueversion"><?php echo Filters::noXSS(L('dueinversion')); ?></label>
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
                <label for="bulk_due_date"><?php echo Filters::noXSS(L('duedate')); ?></label>
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
                <label for="bulk_projects"><?php echo Filters::noXSS(L('attachedtoproject')); ?></label>
                <select id="bulk_projects" name="bulk_projects">
                    <?php echo tpl_options($projectsList); ?>

                </select>
            </li>
            </li>

        </ul>

        <button type="submit" name="updateselectedtasks" value="true"><?php echo Filters::noXSS(L('updateselectedtasks')); ?></button>
    </fieldset>
    <fieldset>
	<legend><b>Close selected tasks</b></legend>
            <div>
                <select class="adminlist" name="resolution_reason" onmouseup="Event.stop(event);">
                    <option value="0"><?php echo Filters::noXSS(L('selectareason')); ?></option>
                    <?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>

                </select>
                <button type="submit" name="updateselectedtasks" value="false">close tasks</button>
                <br/>
                <label class="default text" for="closure_comment"><?php echo Filters::noXSS(L('closurecomment')); ?></label>
                <textarea class="text" id="closure_comment" name="closure_comment" rows="3"
                          cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
                <label><?php echo tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close'))); ?>&nbsp;&nbsp;<?php echo Filters::noXSS(L('mark100')); ?></label>
            </div>
    </fiedlset>

</div>

<?php endif ?>
<?php endif ?>
</div>
</form>
</div>
