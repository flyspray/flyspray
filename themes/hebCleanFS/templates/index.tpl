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
<div id="search">
  <map id="projectsearchform" name="projectsearchform">
    <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
      <div>
        <input class="text" id="searchtext" name="string" type="text" size="20"
               maxlength="100" value="<?php echo Filters::noXSS(Get::val('string')); ?>" accesskey="q" />
		<button id="searchthisproject" type="submit"><?php echo Filters::noXSS(L('searchthisproject')); ?></button>

        <input type="hidden" name="project" value="<?php echo Filters::noXSS(Get::num('project', $proj->id)); ?>" />

        <span id="searchstate" style="cursor:pointer">
	        <a onclick="toggleSearchBox('<?php echo Filters::noJsXSS($this->themeUrl()); ?>');return false;" href="<?php echo Filters::noXSS(CreateUrl('project', $proj->id, null, array_merge($_GET, array('toggleadvanced' => 1)))); ?>">
						<span id="advancedsearchstate" class="showstate">
							<img id="advancedsearchstateimg" src="<?php echo (Cookie::val('advancedsearch')) ? $this->get_image('edit_remove') : $this->get_image('edit_add'); ?>"
							 alt="<?php echo (Cookie::val('advancedsearch')) ? '-' : '+'; ?>"  />
						</span><?php echo Filters::noXSS(L('advanced')); ?>

					</a>
        </span>

        <div id="sc2" class="switchcontent" <?php if (!Cookie::val('advancedsearch')):?>style="display:none;"<?php endif; ?> >

					<?php if (!$user->isAnon()): ?>
						<fieldset>
							<div class="save_search"><label for="save_search" id="lblsaveas"><?php echo Filters::noXSS(L('saveas')); ?></label>
							<input class="text" type="text" value="<?php echo Filters::noXSS(Get::val('search_name')); ?>" id="save_search" name="search_name" size="15" />
							&nbsp;<button onclick="savesearch('<?php echo Filters::escapeqs($_SERVER['QUERY_STRING']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', '<?php echo Filters::noXSS(L('saving')); ?>')" type="button"><?php echo Filters::noXSS(L('OK')); ?></button>
							</div>
						</fieldset>
					<?php endif; ?>


					<fieldset class="advsearch_misc"><legend><?php echo Filters::noXSS(L('miscellaneous')); ?></legend>
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
					</fieldset>
	
					<fieldset class="advsearch_task"><legend><?php echo Filters::noXSS(L('taskproperties')); ?></legend>
						<div class="search_select">
							<label class="default multisel" for="type"><?php echo Filters::noXSS(L('tasktype')); ?></label>
							<select name="type[]" id="type" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('alltasktypes')) + $proj->listTaskTypes(), Get::val('type', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="sev"><?php echo Filters::noXSS(L('severity')); ?></label>
							<select name="sev[]" id="sev" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('allseverities')) + $fs->severities, Get::val('sev', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="pri"><?php echo Filters::noXSS(L('priority')); ?></label>
							<select name="pri[]" id="pri" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('allpriorities')) + $fs->priorities, Get::val('pri', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="due"><?php echo Filters::noXSS(L('dueversion')); ?></label>
							<select name="due[]" id="due" multiple="multiple" size="5">
								<?php echo tpl_options(array_merge(array('' => L('dueanyversion'), 0 => L('unassigned')), $proj->listVersions(false)), Get::val('due', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="reported"><?php echo Filters::noXSS(L('reportedversion')); ?></label>
							<select name="reported[]" id="reported" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('anyversion')) + $proj->listVersions(false), Get::val('reported', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="cat"><?php echo Filters::noXSS(L('category')); ?></label>
							<select name="cat[]" id="cat" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('allcategories')) + $proj->listCategories(), Get::val('cat', '')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="status"><?php echo Filters::noXSS(L('status')); ?></label>
							<select name="status[]" id="status" multiple="multiple" size="5">
								<?php echo tpl_options(array('' => L('allstatuses')) +
															array('open' => L('allopentasks')) +
															array('closed' => L('allclosedtasks')) +
															$proj->listTaskStatuses(), Get::val('status', 'open')); ?>

							</select>
						</div>
		
						<div class="search_select">
							<label class="default multisel" for="percent"><?php echo Filters::noXSS(L('percentcomplete')); ?></label>
							<select name="percent[]" id="percent" multiple="multiple" size="5">
								<?php $percentages = array(); for ($i = 0; $i <= 100; $i += 10) $percentages[$i] = $i; ?>
								<?php echo tpl_options(array('' => L('anyprogress')) + $percentages, Get::val('percent', '')); ?>

							</select>
							</div>
						<div class="clear"></div>
					</fieldset>

        <fieldset class="advsearch_users"><legend><?php echo Filters::noXSS(L('users')); ?></legend>
					<label class="default multisel" for="opened"><?php echo Filters::noXSS(L('openedby')); ?></label>
					<?php echo tpl_userselect('opened', Get::val('opened'), 'opened'); ?>

	
					<label class="default multisel" for="dev"><?php echo Filters::noXSS(L('assignedto')); ?></label>
					<?php echo tpl_userselect('dev', Get::val('dev'), 'dev'); ?>

	
					<label class="default multisel" for="closed"><?php echo Filters::noXSS(L('closedby')); ?></label>
					<?php echo tpl_userselect('closed', Get::val('closed'), 'closed'); ?>

        </fieldset>

        <fieldset class="advsearch_dates"><legend><?php echo Filters::noXSS(L('dates')); ?></legend>
					<div class="dateselect">
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
       <input type="hidden" name="do" value="index" />
      </div>
    </form>
  </map>
</div>
<?php endif; ?>

<div id="tasklist">
  <form action="<?php echo Filters::noXSS(CreateURL('project', $proj->id, null, array('do' => 'index'))); ?>" id="massops" method="post">
    <div>
      <table id="tasklist_table">
        <thead>
          <tr>
            <th class="caret">
            </th>
            <?php if (!$user->isAnon()): ?>
            <th class="ttcolumn">
                <?php if (!$user->isAnon() && $total): ?>
                <a title="<?php echo Filters::noXSS(L('toggleselected')); ?>" href="javascript:ToggleSelected('massops')">
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
        <?php foreach ($tasks as $task_details):?>
        <tr id="task<?php echo $task_details['task_id']; ?>" class="severity<?php echo Filters::noXSS($task_details['task_severity']); ?>">
          <td class="caret">
          </td>
          <?php if (!$user->isAnon()): ?>
          <td class="ttcolumn">
            <input class="ticktask" type="checkbox" name="ids[]" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
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

          </td>
          <td id="numbers">
            <?php echo pagenums($pagenum, $perpage, $total); ?>

          </td>
          <?php else: ?>
          <td id="taskrange"><strong><?php echo Filters::noXSS(L('noresults')); ?></strong></td>
          <?php endif; ?>
        </tr>
      </table>
      <?php if (!$user->isAnon() && $total): ?>
      <div id="massopsactions">
        <select name="action">
          <option value="details.add_notification"><?php echo Filters::noXSS(L('watchtasks')); ?></option>
          <option value="remove_notification"><?php echo Filters::noXSS(L('stopwatchingtasks')); ?></option>
          <option value="takeownership"><?php echo Filters::noXSS(L('assigntaskstome')); ?></option>
        </select>
        <input type="hidden" name="user_id" value="<?php echo Filters::noXSS($user->id); ?>" />
        <button type="submit"><?php echo Filters::noXSS(L('takeaction')); ?></button>
      </div>
      <?php endif ?>
    </div>
  </form>
</div>
