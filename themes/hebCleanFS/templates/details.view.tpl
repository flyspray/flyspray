<div id="actionbar">
	<?php if ($task_details['is_closed']): //if task is closed ?>

		<?php if ($user->can_close_task($task_details)): ?>
			<a class="button" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=reopen&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>"><?php echo Filters::noXSS(L('reopenthistask')); ?></a>
		<?php elseif (!$user->isAnon() && !Flyspray::adminRequestCheck(2, $task_details['task_id'])): ?>
			<a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');"><?php echo Filters::noXSS(L('reopenrequest')); ?></a>
							<div id="closeform" class="popup hide">
								<form name="form3" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="requestreopen" />
										<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
										<label for="reason"><?php echo Filters::noXSS(L('reasonforreq')); ?></label>
										<textarea id="reason" name="reason_given"></textarea><br />
										<button type="submit"><?php echo Filters::noXSS(L('submitreq')); ?></button>
									</div>
								</form>
							</div>
		<?php endif; ?>

	<?php else:  //if task is open  ?>

		<?php if ($user->can_close_task($task_details) && !$d_open): ?>
			<a href="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'], null, array('showclose' => !Req::val('showclose')))); ?>" id="closetask" class="button main" accesskey="y" onclick="showhidestuff('closeform');return false;"> <?php echo Filters::noXSS(L('closetask')); ?></a>
							<div id="closeform" class="<?php if (Req::val('action') != 'details.close' && !Req::val('showclose')): ?>hide <?php endif; ?>popup">
								<form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="details.close" />
										<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
										<select class="adminlist" name="resolution_reason" onmouseup="Event.stop(event);">
											<option value="0"><?php echo Filters::noXSS(L('selectareason')); ?></option>
											<?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>

										</select>
										<button type="submit"><?php echo Filters::noXSS(L('closetask')); ?></button><br />
										<label class="default text" for="closure_comment"><?php echo Filters::noXSS(L('closurecomment')); ?></label>
										<textarea class="text" id="closure_comment" name="closure_comment" rows="3" cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
										<?php if($task_details['percent_complete'] != '100'): ?>
													<label><?php echo tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close'))); ?>&nbsp;&nbsp;<?php echo Filters::noXSS(L('mark100')); ?></label>
													<?php endif; ?>
									</div>
								</form>
							</div>

		<?php elseif (!$d_open && !$user->isAnon() && !Flyspray::AdminRequestCheck(1, $task_details['task_id'])): ?>
			<a href="#close" id="reqclose" class="button main" onclick="showhidestuff('closeform');"><?php echo Filters::noXSS(L('requestclose')); ?></a>
							<div id="closeform" class="popup hide">
								<form name="form3" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post" id="formclosetask">
									<div>
										<input type="hidden" name="action" value="requestclose" />
										<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
										<label for="reason"><?php echo Filters::noXSS(L('reasonforreq')); ?></label>
										<textarea id="reason" name="reason_given"></textarea><br />
										<button type="submit"><?php echo Filters::noXSS(L('submitreq')); ?></button>
									</div>
								</form>
							</div>

		<?php endif; ?>
		
		<?php if ($user->can_edit_task($task_details)): ?>
			<a id="edittask" class="button" href="<?php echo Filters::noXSS(CreateURL('edittask', $task_details['task_id'])); ?>"> <?php echo Filters::noXSS(L('edittask')); ?></a>
		<?php endif; ?>

		<?php if ($user->can_take_ownership($task_details)): ?>
			<a id="own" class="button" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=takeownership&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('assigntome')); ?></a>
		<?php endif; ?>

		<?php if ($user->can_add_to_assignees($task_details) && !empty($task_details['assigned_to'])): ?>
			<a id="own_add" class="button" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=addtoassignees&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('addmetoassignees')); ?></a>
		<?php endif; ?>

	<?php endif; ?>
</div>



<div id="taskdetails">
	<span id="navigation"> <?php if ($prev_id): ?>
		<?php echo tpl_tasklink($prev_id, L('previoustask'), false, array('id'=>'prev', 'accesskey' => 'p')); ?>

		<?php endif; ?>
		<?php if ($prev_id): ?> | <?php endif; ?>
		<?php 
		if($_COOKIE['tasklist_type'] == 'project'):
			$params = $_GET; unset($params['do'], $params['action'], $params['task_id'], $params['switch'], $params['project']); 
			?>
			<a href="<?php echo Filters::noXSS(CreateUrl('project', $proj->id, null, array('do' => 'index') + $params)); ?>"><?php echo Filters::noXSS(L('tasklist')); ?></a>
			<?php endif; ?>
		<?php if ($_COOKIE['tasklist_type'] == 'assignedtome'): ?>
		 <a href="<?php echo Filters::noXSS(CreateURL('project', $proj->id, null, array('do' => 'index', 'dev' => $user->id))); ?>">My Assigned Tasks</a>
		<?php endif; ?>
		
		<?php if ($next_id): ?> | <?php endif; ?>
		<?php if ($next_id): ?>
		<?php echo tpl_tasklink($next_id, L('nexttask'), false, array('id'=>'next', 'accesskey' => 'n')); ?>

		<?php endif; ?>
	</span>

  <div id="taskfields">
	 <ul class="fieldslist">
		<li>
			<span class="value">
				<?php if ($task_details['is_closed']): ?>
				<?php echo Filters::noXSS(L('closed')); ?>

				<?php else: ?>
				<?php echo Filters::noXSS($task_details['status_name']); ?>

								<?php if ($reopened): ?>
								 &nbsp; <strong class="reopened"><?php echo Filters::noXSS(L('reopened')); ?></strong>
								<?php endif; ?>
				<?php endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('status')); ?></span>
		</li>
		<li>
			<span class="value">
				<div class="progress_bar_container" style="width: 90px">
					<span><?php echo Filters::noXSS($task_details['percent_complete']); ?>%</span>
					<div class="progress_bar" style="width:<?php echo Filters::noXSS($task_details['percent_complete']); ?>%"></div>
				</div>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('percentcomplete')); ?></span>
		</li>
	</ul>
	<ul class="fieldslist">
		<li>
			<span class="value"><?php echo Filters::noXSS($task_details['tasktype_name']); ?></span>
			<span class="label"><?php echo Filters::noXSS(L('tasktype')); ?></span>
		</li>
		<li>
			<span class="value">
				<?php foreach ($parent as $cat): ?>
				<?php echo Filters::noXSS($cat['category_name']); ?> &#8594;
				<?php endforeach; ?>
				<?php echo Filters::noXSS($task_details['category_name']); ?>

			</span>
			<span class="label"><?php echo Filters::noXSS(L('category')); ?></span>
		</li>
		<li>
			<span class="value">
				<?php if (empty($assigned_users)): ?>
				<?php echo Filters::noXSS(L('noone')); ?>

				<?php else:
				foreach ($assigned_users as $userid):
				?>
				<?php echo tpl_userlink($userid); ?><br />
				<?php endforeach;
				endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('assignedto')); ?></span>
		</li>
		<li>
			<span class="value"><?php echo Filters::noXSS($task_details['os_name']); ?></span>
			<span class="label"><?php echo Filters::noXSS(L('operatingsystem')); ?></span>
		</li>
		<li>
			<span class="value"><?php echo Filters::noXSS($task_details['severity_name']); ?></span>
			<span class="label"><?php echo Filters::noXSS(L('severity')); ?></span>
		</li>
		<li>
			<span class="label"><?php echo Filters::noXSS(L('priority')); ?></span>
			<span class="value"><?php echo Filters::noXSS($task_details['priority_name']); ?></span>
		</li>
		<li>
			<span class="value"><?php echo Filters::noXSS($task_details['reported_version_name']); ?></span>
			<span class="label"><?php echo Filters::noXSS(L('reportedversion')); ?></span>
		</li>
		<li>
			<span class="value"><?php if ($task_details['due_in_version_name']): ?>
				<?php echo Filters::noXSS($task_details['due_in_version_name']); ?>

				<?php else: ?>
				<?php echo Filters::noXSS(L('undecided')); ?>

				<?php endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('dueinversion')); ?></span>
		</li>
		<li>
			<span class="value"><?php echo Filters::noXSS(formatDate($task_details['due_date'], false, L('undecided'))); ?></span>
			<span class="label"><?php echo Filters::noXSS(L('duedate')); ?></span>
		</li>
	</ul>
	<ul class="fieldslist">
		<li class="votes">
			<span class="value">
					<?php if (count($votes)): ?>
					<a href="javascript:showhidestuff('showvotes')"><?php echo Filters::noXSS(count($votes)); ?> </a>
					<div id="showvotes" class="hide">
							<ul class="reports">
							<?php foreach ($votes as $vote): ?>
								<li><?php echo tpl_userlink($vote); ?> (<?php echo Filters::noXSS(formatDate($vote['date_time'])); ?>)</li>
							<?php endforeach; ?>
							</ul>
					</div>
					<?php else: ?>
					0
					<?php endif; ?>
					<?php if ($user->can_vote($task_details) > 0): ?>
					<a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=details.addvote&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
						(<?php echo Filters::noXSS(L('addvote')); ?>)</a>
					<?php elseif ($user->can_vote($task_details) == -2): ?>
					(<?php echo Filters::noXSS(L('alreadyvotedthistask')); ?>)
					<?php elseif ($user->can_vote($task_details) == -3): ?>
					(<?php echo Filters::noXSS(L('alreadyvotedthisday')); ?>)
					<?php endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('votes')); ?></span>
		</li>
		<li>
			<span class="value">
						<?php if ($task_details['mark_private']): ?>
						<?php echo Filters::noXSS(L('yes')); ?>

						<?php else: ?>
						<?php echo Filters::noXSS(L('no')); ?>

						<?php endif; ?>
				
						<?php if ($user->can_change_private($task_details) && $task_details['mark_private']): ?>
						<a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=makepublic&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
						(<?php echo Filters::noXSS(L('makepublic')); ?>)</a>
						<?php elseif ($user->can_change_private($task_details) && !$task_details['mark_private']): ?>
						<a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=makeprivate&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
							 (<?php echo Filters::noXSS(L('makeprivate')); ?>)</a>
						<?php endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('private')); ?></span>
		</li>
		<?php if (!$user->isAnon()): ?>
		<li>
			<span class="value">
							<?php if ($watched): ?>
								<?php echo Filters::noXSS(L('yes')); ?>

							<?php else: ?>
								<?php echo Filters::noXSS(L('no')); ?>

							<?php endif; ?>
				
							<?php if (!$watched): ?>
								<a accesskey="w" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=details.add_notification&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($user->id); ?>"> (<?php echo Filters::noXSS(L('watchtask')); ?>)</a>
							<?php else: ?>
								<a accesskey="w" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=remove_notification&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($user->id); ?>"> (<?php echo Filters::noXSS(L('stopwatching')); ?>)</a>
							<?php endif; ?>
			</span>
			<span class="label"><?php echo Filters::noXSS(L('watching')); ?></span>
		</li>
		<?php endif; ?>
	 </ul>

  <div id="fineprint">
		<?php echo Filters::noXSS(L('attachedtoproject')); ?>: <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?project=<?php echo Filters::noXSS($task_details['project_id']); ?>"><?php echo Filters::noXSS($task_details['project_title']); ?></a>
		<br />
		<?php echo Filters::noXSS(L('openedby')); ?> <?php echo tpl_userlink($task_details['opened_by']); ?>

			<?php if ($task_details['anon_email'] && $user->perms('view_tasks')): ?>
				(<?php echo Filters::noXSS($task_details['anon_email']); ?>)
			<?php endif; ?>
			- <span title="<?php echo Filters::noXSS(formatDate($task_details['date_opened'], true)); ?>"><?php echo Filters::noXSS(formatDate($task_details['date_opened'], false)); ?></span>
		<?php if ($task_details['last_edited_by']): ?>
		<br />
		<?php echo Filters::noXSS(L('editedby')); ?>  <?php echo tpl_userlink($task_details['last_edited_by']); ?>

			- <span title="<?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], true)); ?>"><?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], false)); ?></span>
		<?php endif; ?>
  </div>









  </div>


  <div id="taskdetailsfull">
		<h2 class="summary severity<?php echo Filters::noXSS($task_details['task_severity']); ?>">
		 FS#<?php echo Filters::noXSS($task_details['task_id']); ?> - <?php echo Filters::noXSS($task_details['item_summary']); ?>

		</h2>
		<!--<h3 class="taskdesc"><?php echo Filters::noXSS(L('details')); ?></h3>-->

     <div id="taskdetailstext"><?php echo $task_text; ?></div>

     <?php $attachments = $proj->listTaskAttachments($task_details['task_id']);
           $this->display('common.attachments.tpl', 'attachments', $attachments); ?>
     <?php $links = $proj->listTaskLinks($task_details['task_id']);
           $this->display('common.links.tpl', 'links', $links); ?>
  </div>


  <div id="taskinfo">
		<div id="taskdeps">
		    <?php if (count($deps) || count($blocks)): ?>
		    <h4><?php echo Filters::noXSS(L('taskdependencies')); ?> (<a class="DoNotPrint" href="<?php echo Filters::noXSS(CreateURL('depends', $task_details['task_id'])); ?>"><?php echo Filters::noXSS(L('viewgraph')); ?></a>):</h4>
			<table>
			<?php foreach ($deps as $dependency): ?>
                <?php $link = tpl_tasklink($dependency, null, true);
                       if(!$link) continue;
				?>
			    <tr>
			      <td>
				    <img src="<?php echo Filters::noXSS($this->get_image('img/gray/dependent_13x12')); ?>" alt="" />
				  </td>
				  <td><?php echo Filters::noXSS(L('dependson')); ?></td>
				  <td> <?php echo $link; ?></td>
				  <td>
				  <?php if ($user->can_edit_task($task_details)): ?>
					<span class="DoNotPrint"> 
						<a class="removedeplink" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=removedep&amp;depend_id=<?php echo Filters::noXSS($dependency['depend_id']); ?>&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
						  <img src="<?php echo Filters::noXSS($this->get_image('button_cancel')); ?>" alt="<?php echo Filters::noXSS(L('remove')); ?>" title="<?php echo Filters::noXSS(L('remove')); ?>" />
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
                    <img src="<?php echo Filters::noXSS($this->get_image('img/gray/blocking_13x12')); ?>" alt="" />
                  </td>
                  <td><?php echo Filters::noXSS(L('blocks')); ?></td>
                  <td> <?php echo $link; ?></td>
                </tr>
            <?php endforeach; ?>		
			</table>
			<?php else: ?>
			<h4><?php echo Filters::noXSS(L('notaskdependencies')); ?></h4>
			<?php endif; ?>
	
			<?php if ($user->can_edit_task($task_details)): ?>
			<form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post">
				<div>
				    <label for="dep_task_id"><?php echo Filters::noXSS(L('newdependency')); ?></label>
					<input type="hidden" name="action" value="details.newdep" />
					<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
					<input class="text" type="text" value="<?php echo Filters::noXSS(Req::val('dep_task_id')); ?>" id="dep_task_id" name="dep_task_id" size="5" maxlength="10" />
					<button type="submit" name="submit"><?php echo Filters::noXSS(L('add')); ?></button>
				</div>
			</form>
			<?php endif; ?>
		</div>
  </div>

  <?php if ($task_details['is_closed']): ?>
  <div id="taskclosed">
      <?php echo Filters::noXSS(L('closedby')); ?>&nbsp;&nbsp;<?php echo tpl_userlink($task_details['closed_by']); ?><br />
      <?php echo Filters::noXSS(formatDate($task_details['date_closed'], true)); ?><br />
      <strong><?php echo Filters::noXSS(L('reasonforclosing')); ?></strong> &nbsp;<?php echo Filters::noXSS($task_details['resolution_name']); ?><br />
      <?php if ($task_details['closure_comment']): ?>
      <strong><?php echo Filters::noXSS(L('closurecomment')); ?></strong> &nbsp;<?php echo wordwrap(TextFormatter::render($task_details['closure_comment'], true), 40, "\n", true); ?>

      <?php endif; ?>
  </div>
  <?php endif; ?>

  <div id="actionbuttons">

	<?php if (count($penreqs)): ?>
		<div class="pendingreq"><strong><?php echo Filters::noXSS(formatDate($penreqs[0]['time_submitted'])); ?>: <?php echo Filters::noXSS(L('request'.$penreqs[0]['request_type'])); ?></strong>
		<?php if ($penreqs[0]['reason_given']): ?>
			<?php echo Filters::noXSS(L('reasonforreq')); ?>: <?php echo Filters::noXSS($penreqs[0]['reason_given']); ?>

		<?php endif; ?>
		</div>
  <?php endif; ?>
  </div>
	
<div class="clear"></div>
</div>

