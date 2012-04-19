<div id="actionbar">
	<?php if ($task_details['is_closed']): //if task is closed ?>

		<?php if ($user->can_close_task($task_details)): ?>
			<a class="button" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=reopen&amp;task_id={$task_details['task_id']}">{L('reopenthistask')}</a>
		<?php elseif (!$user->isAnon() && !Flyspray::adminRequestCheck(2, $task_details['task_id'])): ?>
			<a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');">{L('reopenrequest')}</a>
							<div id="closeform" class="popup hide">
								<form name="form3" action="{CreateUrl('details', $task_details['task_id'])}" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="requestreopen" />
										<input type="hidden" name="task_id" value="{$task_details['task_id']}" />
										<label for="reason">{L('reasonforreq')}</label>
										<textarea id="reason" name="reason_given"></textarea><br />
										<button type="submit">{L('submitreq')}</button>
									</div>
								</form>
							</div>
		<?php endif; ?>

	<?php else:  //if task is open  ?>

		<?php if ($user->can_close_task($task_details) && !$d_open): ?>
			<a href="{CreateUrl('details', $task_details['task_id'], null, array('showclose' => !Req::val('showclose')))}" id="closetask" class="button main" accesskey="y" onclick="showhidestuff('closeform');return false;"> {L('closetask')}</a>
							<div id="closeform" class="<?php if (Req::val('action') != 'details.close' && !Req::val('showclose')): ?>hide <?php endif; ?>popup">
								<form action="{CreateUrl('details', $task_details['task_id'])}" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="details.close" />
										<input type="hidden" name="task_id" value="{$task_details['task_id']}" />
										<select class="adminlist" name="resolution_reason" onmouseup="Event.stop(event);">
											<option value="0">{L('selectareason')}</option>
											{!tpl_options($proj->listResolutions(), Req::val('resolution_reason'))}
										</select>
										<button type="submit">{L('closetask')}</button><br />
										<label class="default text" for="closure_comment">{L('closurecomment')}</label>
										<textarea class="text" id="closure_comment" name="closure_comment" rows="3" cols="25">{Req::val('closure_comment')}</textarea>
										<?php if($task_details['percent_complete'] != '100'): ?>
													<label>{!tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close')))}&nbsp;&nbsp;{L('mark100')}</label>
													<?php endif; ?>
									</div>
								</form>
							</div>

		<?php elseif (!$d_open && !$user->isAnon() && !Flyspray::AdminRequestCheck(1, $task_details['task_id'])): ?>
			<a href="#close" id="reqclose" class="button main" onclick="showhidestuff('closeform');">{L('requestclose')}</a>
							<div id="closeform" class="popup hide">
								<form name="form3" action="{CreateUrl('details', $task_details['task_id'])}" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="requestclose" />
										<input type="hidden" name="task_id" value="{$task_details['task_id']}" />
										<label for="reason">{L('reasonforreq')}</label>
										<textarea id="reason" name="reason_given"></textarea><br />
										<button type="submit">{L('submitreq')}</button>
									</div>
								</form>
							</div>

		<?php endif; ?>
		
		<?php if ($user->can_edit_task($task_details)): ?>
			<a id="edittask" class="button" href="{CreateURL('edittask', $task_details['task_id'])}"> {L('edittask')}</a>
		<?php endif; ?>

		<?php if ($user->can_take_ownership($task_details)): ?>
			<a id="own" class="button" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=takeownership&amp;ids={$task_details['task_id']}"> {L('assigntome')}</a>
		<?php endif; ?>

		<?php if ($user->can_add_to_assignees($task_details) && !empty($task_details['assigned_to'])): ?>
			<a id="own_add" class="button" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=addtoassignees&amp;ids={$task_details['task_id']}"> {L('addmetoassignees')}</a>
		<?php endif; ?>

	<?php endif; ?>
</div>



<div id="taskdetails">
	<span id="navigation"> <?php if ($prev_id): ?>
		{!tpl_tasklink($prev_id, L('previoustask'), false, array('id'=>'prev', 'accesskey' => 'p'))}
		<?php endif; ?>
		<?php if ($prev_id): ?> | <?php endif; ?>
		<?php 
		if($_COOKIE['tasklist_type'] == 'project'):
			$params = $_GET; unset($params['do'], $params['action'], $params['task_id'], $params['switch'], $params['project']); 
			?>
			<a href="{CreateUrl('project', $proj->id, null, array('do' => 'index') + $params)}">{L('tasklist')}</a>
			<?php endif; ?>
		<?php if ($_COOKIE['tasklist_type'] == 'assignedtome'): ?>
		 <a href="{CreateURL('project', $proj->id, null, array('do' => 'index', 'dev' => $user->id))}">My Assigned Tasks</a>
		<?php endif; ?>
		
		<?php if ($next_id): ?> | <?php endif; ?>
		<?php if ($next_id): ?>
		{!tpl_tasklink($next_id, L('nexttask'), false, array('id'=>'next', 'accesskey' => 'n'))}
		<?php endif; ?>
	</span>

  <div id="taskfields">
	 <ul class="fieldslist">
		<li>
			<span class="label">{L('status')}</span>
			<span class="value">
				<?php if ($task_details['is_closed']): ?>
				{L('closed')}
				<?php else: ?>
				{$task_details['status_name']}
								<?php if ($reopened): ?>
								 &nbsp; <strong class="reopened">{L('reopened')}</strong>
								<?php endif; ?>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="label">{L('percentcomplete')}</span>
			<span class="value">
				<div class="progress_bar_container" style="width: 90px">
					<span>{$task_details['percent_complete']}%</span>
					<div class="progress_bar" style="width:{$task_details['percent_complete']}%"></div>
				</div>
			</span>
		</li>
	</ul>
	<ul class="fieldslist">
		<li>
			<span class="label">{L('tasktype')}</span>
			<span class="value">{$task_details['tasktype_name']}</span>
		</li>
		<li>
			<span class="label">{L('category')}</span>
			<span class="value">
				<?php foreach ($parent as $cat): ?>
				{$cat['category_name']} &#8594;
				<?php endforeach; ?>
				{$task_details['category_name']}
			</span>
		</li>
		<li>
			<span class="label">{L('assignedto')}</span>
			<span class="value">
				<?php if (empty($assigned_users)): ?>
				{L('noone')}
				<?php else:
				foreach ($assigned_users as $userid):
				?>
				{!tpl_userlink($userid)}<br />
				<?php endforeach;
				endif; ?>
			</span>
		</li>
		<li>
			<span class="label">{L('operatingsystem')}</span>
			<span class="value">{$task_details['os_name']}</span>
		</li>
		<li>
			<span class="label">{L('severity')}</span>
			<span class="value">{$task_details['severity_name']}</span>
		</li>
		<li>
			<span class="label">{L('priority')}</span>
			<span class="value">{$task_details['priority_name']}</span>
		</li>
		<li>
			<span class="label">{L('reportedversion')}</span>
			<span class="value">{$task_details['reported_version_name']}</span>
		</li>
		<li>
			<span class="label">{L('dueinversion')}</span>
			<span class="value"><?php if ($task_details['due_in_version_name']): ?>
				{$task_details['due_in_version_name']}
				<?php else: ?>
				{L('undecided')}
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="label">{L('duedate')}</span>
			<span class="value">{formatDate($task_details['due_date'], false, L('undecided'))}</span>
		</li>
	</ul>
	<ul class="fieldslist">
		<li class="votes">
			<span class="label">{L('votes')}</span>
			<span class="value">
					<?php if (count($votes)): ?>
					<a href="javascript:showhidestuff('showvotes')">{count($votes)} </a>
					<div id="showvotes" class="hide">
							<ul class="reports">
							<?php foreach ($votes as $vote): ?>
								<li>{!tpl_userlink($vote)} ({formatDate($vote['date_time'])})</li>
							<?php endforeach; ?>
							</ul>
					</div>
					<?php else: ?>
					0
					<?php endif; ?>
					<?php if ($user->can_vote($task_details) > 0): ?>
					<a href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=details.addvote&amp;task_id={$task_details['task_id']}">
						({L('addvote')})</a>
					<?php elseif ($user->can_vote($task_details) == -2): ?>
					({L('alreadyvotedthistask')})
					<?php elseif ($user->can_vote($task_details) == -3): ?>
					({L('alreadyvotedthisday')})
					<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="label">{L('private')}</span>
			<span class="value">
						<?php if ($task_details['mark_private']): ?>
						{L('yes')}
						<?php else: ?>
						{L('no')}
						<?php endif; ?>
				
						<?php if ($user->can_change_private($task_details) && $task_details['mark_private']): ?>
						<a href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=makepublic&amp;task_id={$task_details['task_id']}">
						({L('makepublic')})</a>
						<?php elseif ($user->can_change_private($task_details) && !$task_details['mark_private']): ?>
						<a href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=makeprivate&amp;task_id={$task_details['task_id']}">
							 ({L('makeprivate')})</a>
						<?php endif; ?>
			</span>
		</li>
		<?php if (!$user->isAnon()): ?>
		<li>
			<span class="label">{L('watching')}</span>
			<span class="value">
							<?php if ($watched): ?>
								{L('yes')}
							<?php else: ?>
								{L('no')}
							<?php endif; ?>
				
							<?php if (!$watched): ?>
								<a accesskey="w" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=details.add_notification&amp;ids={$task_details['task_id']}&amp;user_id={$user->id}"> ({L('watchtask')})</a>
							<?php else: ?>
								<a accesskey="w" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=remove_notification&amp;ids={$task_details['task_id']}&amp;user_id={$user->id}"> ({L('stopwatching')})</a>
							<?php endif; ?>
			</span>
		</li>
		<?php endif; ?>
	 </ul>

  <div id="fineprint">
		{L('attachedtoproject')}: <a href="{$_SERVER['SCRIPT_NAME']}?project={$task_details['project_id']}">{$task_details['project_title']}</a>
		<br />
		{L('openedby')} {!tpl_userlink($task_details['opened_by'])}
			<?php if ($task_details['anon_email'] && $user->perms('view_tasks')): ?>
				({$task_details['anon_email']})
			<?php endif; ?>
			- <span title="{formatDate($task_details['date_opened'], true)}">{formatDate($task_details['date_opened'], false)}</span>
		<?php if ($task_details['last_edited_by']): ?>
		<br />
		{L('editedby')}  {!tpl_userlink($task_details['last_edited_by'])}
			- <span title="{formatDate($task_details['last_edited_time'], true)}">{formatDate($task_details['last_edited_time'], false)}</span>
		<?php endif; ?>
  </div>









  </div>


  <div id="taskdetailsfull">
		<h2 class="summary severity{$task_details['task_severity']}">
		 FS#{$task_details['task_id']} - {$task_details['item_summary']}
		</h2>
		<!--<h3 class="taskdesc">{L('details')}</h3>-->

     <div id="taskdetailstext">{!$task_text}</div>

     <?php $attachments = $proj->listTaskAttachments($task_details['task_id']);
           $this->display('common.attachments.tpl', 'attachments', $attachments); ?>
  </div>


  <div id="taskinfo">
		<div id="taskdeps">
		    <?php if (count($deps) || count($blocks)): ?>
		    <h4>{L('taskdependencies')} (<a class="DoNotPrint" href="{CreateURL('depends', $task_details['task_id'])}">{L('viewgraph')}</a>):</h4>
			<table>
			<?php foreach ($deps as $dependency): ?>
                <?php $link = tpl_tasklink($dependency, null, true);
                       if(!$link) continue;
				?>
			    <tr>
			      <td>
				    <img src="{$this->get_image('img/gray/dependent_13x12')}" alt="" />
				  </td>
				  <td>{L('dependson')}</td>
				  <td> {!$link}</td>
				  <td>
				  <?php if ($user->can_edit_task($task_details)): ?>
					<span class="DoNotPrint"> 
						<a class="removedeplink" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=removedep&amp;depend_id={$dependency['depend_id']}&amp;task_id={$task_details['task_id']}">
						  <img src="{$this->get_image('button_cancel')}" alt="{L('remove')}" title="{L('remove')}" />
						</a>
					</span>
				  <?php endif; ?>
				  </td>
				</tr>
			<?php endforeach; ?>

			<?php foreach ($blocks as $block): ?>
            <?php $link = tpl_tasklink($block, null, true);
                   if(!$link) continue;
            ?>
                <tr>
			      <td>
                    <img src="{$this->get_image('img/gray/blocking_13x12')}" alt="" />
                  </td>
                  <td>{L('blocks')}</td>
                  <td> {!$link}</td>
                </tr>
            <?php endforeach; ?>		
			</table>
			<?php else: ?>
			<h4>{L('notaskdependencies')}</h4>
			<?php endif; ?>
	
			<?php if ($user->can_edit_task($task_details)): ?>
			<form action="{CreateUrl('details', $task_details['task_id'])}" method="post">
				<div>
				    <label for="dep_task_id">{L('newdependency')}</label>
					<input type="hidden" name="action" value="details.newdep" />
					<input type="hidden" name="task_id" value="{$task_details['task_id']}" />
					<input class="text" type="text" value="{Req::val('dep_task_id')}" id="dep_task_id" name="dep_task_id" size="5" maxlength="10" />
					<button type="submit" name="submit">{L('add')}</button>
				</div>
			</form>
			<?php endif; ?>
		</div>
  </div>

  <?php if ($task_details['is_closed']): ?>
  <div id="taskclosed">
      {L('closedby')}&nbsp;&nbsp;{!tpl_userlink($task_details['closed_by'])}<br />
      {formatDate($task_details['date_closed'], true)}<br />
      <strong>{L('reasonforclosing')}</strong> &nbsp;{$task_details['resolution_name']}<br />
      <?php if ($task_details['closure_comment']): ?>
      <strong>{L('closurecomment')}</strong> &nbsp;{!wordwrap(TextFormatter::render($task_details['closure_comment'], true), 40, "\n", true)}
      <?php endif; ?>
  </div>
  <?php endif; ?>

  <div id="actionbuttons">

	<?php if (count($penreqs)): ?>
		<div class="pendingreq"><strong>{formatDate($penreqs[0]['time_submitted'])}: {L('request'.$penreqs[0]['request_type'])}</strong>
		<?php if ($penreqs[0]['reason_given']): ?>
			{L('reasonforreq')}: {$penreqs[0]['reason_given']}
		<?php endif; ?>
		</div>
  <?php endif; ?>
  </div>
	
<div class="clear"></div>
</div>

