<div id="taskdetails">
<span id="navigation"> <?php if ($prev_id): ?>
  {!tpl_tasklink($prev_id, L('previoustask'), false, array('id'=>'prev', 'accesskey' => 'p'))}
  <?php endif; ?>
  <?php if ($prev_id): ?> | <?php endif; ?>
  <?php $params = $_GET; unset($params['do'], $params['action'], $params['task_id'], $params['switch'], $params['project']); ?>
  <a href="{CreateUrl('project', $proj->id, null, array('do' => 'index') + $params)}">{L('tasklist')}</a>
  <?php if ($next_id): ?> | <?php endif; ?>
  <?php if ($next_id): ?>
  {!tpl_tasklink($next_id, L('nexttask'), false, array('id'=>'next', 'accesskey' => 'n'))}
  <?php endif; ?>
</span>

  <h2 class="summary severity{$task_details['task_severity']}">
	 FS#{$task_details['task_id']} - {$task_details['item_summary']}
  </h2>

  <div id="fineprint">
	 {L('attachedtoproject')}:
	 <a href="{$_SERVER['SCRIPT_NAME']}?project={$task_details['project_id']}">{$task_details['project_title']}</a>
	 <br />
	 {L('openedby')} {!tpl_userlink($task_details['opened_by'])}
     <?php if ($task_details['anon_email'] && $user->perms('view_tasks')): ?>
     ({$task_details['anon_email']})
     <?php endif; ?>
	 - {formatDate($task_details['date_opened'], true)}
	 <?php if ($task_details['last_edited_by']): ?>
	 <br />
	 {L('editedby')}  {!tpl_userlink($task_details['last_edited_by'])}
	 - {formatDate($task_details['last_edited_time'], true)}
	 <?php endif; ?>
  </div>

  <table><tr><td id="taskfieldscell"><?php // small layout table ?>

  <div id="taskfields">
	 <table>
		<tr>
		  <th id="tasktype">{L('tasktype')}</th>
		  <td headers="tasktype">{$task_details['tasktype_name']}</td>
		</tr>
		<tr>
		  <th id="category">{L('category')}</th>
		  <td headers="category">
			 <?php foreach ($parent as $cat): ?>
			 {$cat['category_name']} &#8594;
			 <?php endforeach; ?>
			 {$task_details['category_name']}
		  </td>
		</tr>
		<tr>
		  <th id="status">{L('status')}</th>
		  <td headers="status">
			 <?php if ($task_details['is_closed']): ?>
			 {L('closed')}
			 <?php else: ?>
			 {$task_details['status_name']}
               <?php if ($reopened): ?>
                &nbsp; <strong class="reopened">{L('reopened')}</strong>
               <?php endif; ?>
			 <?php endif; ?>
		  </td>
		</tr>
		<tr>
		  <th id="assignedto">{L('assignedto')}</th>
		  <td headers="assignedto">
			 <?php if (empty($assigned_users)): ?>
			 {L('noone')}
			 <?php else:
			 foreach ($assigned_users as $userid):
			 ?>
			 {!tpl_userlink($userid)}<br />
			 <?php endforeach;
			 endif; ?>
		  </td>
		</tr>
		<tr>
		  <th id="os">{L('operatingsystem')}</th>
		  <td headers="os">{$task_details['os_name']}</td>
		</tr>
		<tr>
		  <th id="severity">{L('severity')}</th>
		  <td headers="severity">{$task_details['severity_name']}</td>
		</tr>
		<tr>
		  <th id="priority">{L('priority')}</th>
		  <td headers="priority">{$task_details['priority_name']}</td>
		</tr>
		<tr>
		  <th id="reportedver">{L('reportedversion')}</th>
		  <td headers="reportedver">{$task_details['reported_version_name']}</td>
		</tr>
		<tr>
		  <th id="dueversion">{L('dueinversion')}</th>
		  <td headers="dueversion">
			 <?php if ($task_details['due_in_version_name']): ?>
			 {$task_details['due_in_version_name']}
			 <?php else: ?>
			 {L('undecided')}
			 <?php endif; ?>
		  </td>
		</tr>
		<tr>
		  <th id="duedate">{L('duedate')}</th>
		  <td headers="duedate">
			 {formatDate($task_details['due_date'], false, L('undecided'))}
		  </td>
		</tr>
		<tr>
		  <th id="percent">{L('percentcomplete')}</th>
		  <td headers="percent">
			 <img src="{$this->get_image('percent-' . $task_details['percent_complete'])}"
				title="{$task_details['percent_complete']}% {L('complete')}"
				alt="{$task_details['percent_complete']}%" />
		  </td>
		</tr>
        <tr class="votes">
		  <th id="votes">{L('votes')}</th>
		  <td headers="votes">
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
          <a href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=details.removevote&amp;task_id={$task_details['task_id']}">
          ({L('removevote')})
          <?php elseif ($user->can_vote($task_details) == -3): ?>
          ({L('alreadyvotedthisday')})
          <?php endif; ?>
          </td>
        </tr>
        <tr>
		  <th id="private">{L('private')}</th>
		  <td headers="private">
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
          </td>
        </tr>
        <?php if (!$user->isAnon()): ?>
        <tr>
		  <th id="watching">{L('watching')}</th>
		  <td headers="watching">
              <?php if ($watched): ?>
              {L('yes')}
              <?php else: ?>
              {L('no')}
              <?php endif; ?>

              <?php if (!$watched): ?>
              <a accesskey="w"
              href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=details.add_notification&amp;ids={$task_details['task_id']}&amp;user_id={$user->id}">
              ({L('watchtask')})</a>
              <?php else: ?>
              <a accesskey="w"
              href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=remove_notification&amp;ids={$task_details['task_id']}&amp;user_id={$user->id}">
              ({L('stopwatching')})</a>
              <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
	 </table>
  </div>

  </td><td>

  <div id="taskdetailsfull">
	 <h3 class="taskdesc">{L('details')}</h3>
     <div id="taskdetailstext">{!$task_text}</div>

     <?php $attachments = $proj->listTaskAttachments($task_details['task_id']);
           $this->display('common.attachments.tpl', 'attachments', $attachments); ?>
  </div>

  </td></tr></table>

  <div id="taskinfo">
	 <div id="taskdeps">
		<b>{L('taskdependson')}</b>
		<br />
		<?php foreach ($deps as $dependency): ?>
		<?php $link = tpl_tasklink($dependency, null, true);
				if(!$link) continue;
		?>
		{!$link}
		<?php if ($user->can_edit_task($task_details)): ?>
		<span class="DoNotPrint"> -
		  <a class="removedeplink"
			 href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=removedep&amp;depend_id={$dependency['depend_id']}&amp;task_id={$task_details['task_id']}">
			 {L('remove')}</a>
		</span>
		<?php endif; ?>
		<br />
		<?php endforeach; ?>

		<br class="DoNotPrint" />

		<?php if (count($deps) || count($blocks)): ?>
		<a class="DoNotPrint" href="{CreateURL('depends', $task_details['task_id'])}">{L('depgraph')}</a>
		<br />
		<br />
		<?php endif; ?>

		<?php if ($user->can_edit_task($task_details)): ?>
		<form action="{CreateUrl('details', $task_details['task_id'])}" method="post">
		  <div>
			 <input type="hidden" name="action" value="details.newdep" />
			 <input type="hidden" name="task_id" value="{$task_details['task_id']}" />
			 <input class="text" type="text" value="{Req::val('dep_task_id')}" name="dep_task_id" size="5" maxlength="10" />
			 <button type="submit" name="submit">{L('addnew')}</button>
		  </div>
		</form>
		<?php endif; ?>
	 </div>

	 <div id="taskblocks">
        <?php if ($blocks): ?>
		<b>{L('taskblocks')}</b>
		<br />
        <?php endif; ?>
		<?php foreach ($blocks as $block): ?>
		<?php $link = tpl_tasklink($block, null, true);
				if(!$link) continue;
		?>
		{!$link}
		<br />
		<?php endforeach; ?>
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
	 <?php if ($task_details['is_closed']): ?>

	 <?php if ($user->can_close_task($task_details)): ?>
	 <a class="button" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=reopen&amp;task_id={$task_details['task_id']}">
		{L('reopenthistask')}</a>
	 <?php elseif (!$user->isAnon() && !Flyspray::adminRequestCheck(2, $task_details['task_id'])): ?>
	 <a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');">
		{L('reopenrequest')}</a>
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

	 <?php else: ?>

	 <?php if ($user->can_close_task($task_details) && !$d_open): ?>
	 <a href="{CreateUrl('details', $task_details['task_id'], null, array('showclose' => !Req::val('showclose')))}" id="closetask" class="button" accesskey="y" onclick="showhidestuff('closeform');return false;">
		{L('closetask')}</a>
     <div id="closeform" class="<?php if (Req::val('action') != 'details.close' && !Req::val('showclose')): ?>hide <?php endif; ?>popup">
		<form action="{CreateUrl('details', $task_details['task_id'])}" method="post" id="formclosetask">
		  <div>
			 <input type="hidden" name="action" value="details.close" />
			 <input type="hidden" name="task_id" value="{$task_details['task_id']}" />
			 <select class="adminlist" name="resolution_reason" onmouseup="Event.stop(event);">
				<option value="0">{L('selectareason')}</option>
				{!tpl_options($proj->listResolutions(), Req::val('resolution_reason'))}
			 </select>
			 <button type="submit">{L('closetask')}</button>
			 <label class="default text" for="closure_comment">{L('closurecomment')}</label>
			 <textarea class="text" id="closure_comment" name="closure_comment" rows="3" cols="25">{Req::val('closure_comment')}</textarea>
			 <?php if($task_details['percent_complete'] != '100'): ?>
             <label>{!tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close')))}&nbsp;&nbsp;{L('mark100')}</label>
             <?php endif; ?>
		  </div>
		</form>
	 </div>
	 <?php elseif (!$d_open && !$user->isAnon() && !Flyspray::AdminRequestCheck(1, $task_details['task_id'])): ?>
	 <a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');">
		{L('requestclose')}</a>
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

	 <?php if ($user->can_take_ownership($task_details)): ?>
	 <a id="own" class="button"
		href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=takeownership&amp;ids={$task_details['task_id']}">
		{L('assigntome')}</a>
	 <?php endif; ?>

	 <?php if ($user->can_add_to_assignees($task_details) && !empty($task_details['assigned_to'])): ?>
	 <a id="own_add" class="button"
		href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;task_id={$task_details['task_id']}&amp;action=addtoassignees&amp;ids={$task_details['task_id']}">
		{L('addmetoassignees')}</a>
	 <?php endif; ?>

	 <?php if ($user->can_edit_task($task_details)): ?>
	 <a id="edittask" class="button" href="{CreateURL('edittask', $task_details['task_id'])}">
		{L('edittask')}</a>
	 <?php endif; ?>

	 <?php endif; ?>
	 <?php if (count($penreqs)): ?>
     <div class="pendingreq"><strong>{formatDate($penreqs[0]['time_submitted'])}: {L('request'.$penreqs[0]['request_type'])}</strong>
     <?php if ($penreqs[0]['reason_given']): ?>
     {L('reasonforreq')}: {$penreqs[0]['reason_given']}
     <?php endif; ?>
     </div>
     <?php endif; ?>
  </div>
</div>
