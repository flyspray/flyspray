<div id="actionbar">
    <?php if ($task_details['is_closed']): //if task is closed ?>

    <?php if ($user->can_close_task($task_details)): ?>
    <a class="button"
       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=reopen&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>"><?php echo Filters::noXSS(L('reopenthistask')); ?></a>
    <?php elseif (!$user->isAnon() && !Flyspray::adminRequestCheck(2, $task_details['task_id'])): ?>
    <a href="#close" id="reqclose" class="button" onclick="showhidestuff('closeform');"><?php echo Filters::noXSS(L('reopenrequest')); ?></a>

    <div id="closeform" class="popup hide">
        <form name="form3" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post" id="formclosetask">
            <div>
                <input type="hidden" name="action" value="requestreopen"/>
                <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
                <label for="reason"><?php echo Filters::noXSS(L('reasonforreq')); ?></label>
                <textarea id="reason" name="reason_given"></textarea><br/>
                <button type="submit"><?php echo Filters::noXSS(L('submitreq')); ?></button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php else:  //if task is open  ?>

    <?php if ($user->can_close_task($task_details) && !$d_open): ?>
    <a href="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'], null, array('showclose' => !Req::val('showclose')))); ?>"
       id="closetask" class="button main" accesskey="y"
       onclick="showhidestuff('closeform');return false;"> <?php echo Filters::noXSS(L('closetask')); ?></a>

    <div id="closeform"
         class="<?php if (Req::val('action') != 'details.close' && !Req::val('showclose')): ?>hide <?php endif; ?>popup">
        <form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post" id="formclosetask">
            <div>
                <input type="hidden" name="action" value="details.close"/>
                <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
                <select class="adminlist" name="resolution_reason" onmouseup="Event.stop(event);">
                    <option value="0"><?php echo Filters::noXSS(L('selectareason')); ?></option>
                    <?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>

                </select>
                <button type="submit"><?php echo Filters::noXSS(L('closetask')); ?></button>
                <br/>
                <label class="default text" for="closure_comment"><?php echo Filters::noXSS(L('closurecomment')); ?></label>
                <textarea class="text" id="closure_comment" name="closure_comment" rows="3"
                          cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
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
                <input type="hidden" name="action" value="requestclose"/>
                <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
                <label for="reason"><?php echo Filters::noXSS(L('reasonforreq')); ?></label>
                <textarea id="reason" name="reason_given"></textarea><br/>
                <button type="submit"><?php echo Filters::noXSS(L('submitreq')); ?></button>
            </div>
        </form>
    </div>
    <?php elseif(!$user->isAnon()): ?>
    <a href="#closedisabled" id="reqclose" class="tooltip button disabled main"><?php echo Filters::noXSS(L('closetask')); ?>

    <span class="custom info">
                    <em><?php echo Filters::noXSS(L('information')); ?></em>
                    <br>
        <?php echo Filters::noXSS(L('taskclosedisabled')); ?>

        <br>
        <?php foreach ($deps as $dependency)
                    {
                        echo "FS#".$dependency['task_id']." : ".$dependency['item_summary']."</br>";
                    }
                    ?>
                </span>
    </a>
    <?php endif; ?>

    <?php if ($user->can_edit_task($task_details)): ?>
    <a id="edittask" class="button" accesskey="e"
       href="<?php echo Filters::noXSS(CreateURL('edittask', $task_details['task_id'])); ?>"> <?php echo Filters::noXSS(L('edittask')); ?></a>
    <?php endif; ?>

    <?php if ($user->can_take_ownership($task_details)): ?>
    <a id="own" class="button"
       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=takeownership&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('assigntome')); ?></a>
    <?php endif; ?>

    <?php if ($user->can_add_to_assignees($task_details) && !empty($task_details['assigned_to'])): ?>
    <a id="own_add" class="button"
       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=addtoassignees&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('addmetoassignees')); ?></a>
    <?php endif; ?>
	<input type="checkbox" id="s_quickactions">
	<label class="button main" id="actions" for="s_quickactions"><?php echo Filters::noXSS(L('quickaction')); ?></label>
	<div id="actionsform">
        <ul>
            <?php if ($user->can_edit_task($task_details)): ?>
            <li>
                <a accesskey="e" href="<?php echo Filters::noXSS(CreateURL('edittask', $task_details['task_id'])); ?>"> <?php echo Filters::noXSS(L('edittask')); ?></a>
            </li>
            <?php endif; ?>

            <?php if ($user->can_edit_task($task_details)): ?>
            <li>
                <a href="#" onclick="showhidestuff('setparentform');"><?php echo Filters::noXSS(L('setparent')); ?></a>

                <div id="setparentform" class="hide">
                    </br>
                    <form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post">
                        <?php echo Filters::noXSS(L('parenttaskid')); ?>

                        <input type="hidden" name="action" value="details.setparent"/>
                        <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
                        <input class="text" type="text" value="" id="supertask_id" name="supertask_id" size="5"
                               maxlength="10"/>
                        <button type="submit" name="submit"><?php echo Filters::noXSS(L('set')); ?></button>
                    </form>
                    </br>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($user->can_edit_task($task_details)): ?>
            <li>
                <a href="#" onclick="showhidestuff('associateform');"><?php echo Filters::noXSS(L('associatesubtask')); ?></a>

                <div id="associateform" class="hide">
                    <br>
                    <form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post">
                        <?php echo Filters::noXSS(L('associatetaskid')); ?>

                        <input type="hidden" name="action" value="details.associatesubtask"/>
                        <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>"/>
                        <input class="text" type="text" value="" id="associate_subtask_id" name="associate_subtask_id"
                               size="5" maxlength="10"/>
                        <button type="submit" name="submit"><?php echo Filters::noXSS(L('set')); ?></button>
                    </form>
                    </br>
                </div>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo Filters::noXSS(CreateURL('depends', $task_details['task_id'])); ?>"><?php echo Filters::noXSS(L('depgraph')); ?></a>
            </li>
            <?php if ($user->can_edit_task($task_details)): ?>
            <li>
                <a href="#" onclick="showhidestuff('adddepform');"><?php echo Filters::noXSS(L('adddependenttask')); ?></a>
                <div id="adddepform" class="hide">
                    <br>
                    <form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post">
                        <label for="dep_task_id"><?php echo Filters::noXSS(L('newdependency')); ?></label>
                        <input type="hidden" name="action" value="details.newdep" />
                        <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
                        <input class="text" type="text" value="<?php echo Filters::noXSS(Req::val('dep_task_id')); ?>" id="dep_task_id" name="dep_task_id" size="5" maxlength="10" />
                        <button type="submit" name="submit"><?php echo Filters::noXSS(L('add')); ?></button>
                    </form>
                    </br>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($proj->id && $user->perms('open_new_tasks')): ?>
            <li>
                <a href="<?php echo Filters::noXSS(CreateURL('newtask', $proj->id, $task_details['task_id'])); ?>"><?php echo Filters::noXSS(L('addnewsubtask')); ?></a>
            </li>
            <?php endif; ?>

            <?php if ($user->can_take_ownership($task_details)): ?>
            <li>
                <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=takeownership&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('assigntome')); ?></a>
            </li>
            <?php endif; ?>


            <?php if ($user->can_add_to_assignees($task_details) && !empty($task_details['assigned_to'])): ?>
            <li>
                <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=addtoassignees&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>"> <?php echo Filters::noXSS(L('addmetoassignees')); ?></a>
            </li>
            <?php endif; ?>


            <?php if ($user->can_vote($task_details) > 0): ?>
            <li>
                <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=details.addvote&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>"><?php echo Filters::noXSS(L('voteforthistask')); ?></a>
            </li>
            <?php endif; ?>


            <?php if (!$user->isAnon()): ?>
            <?php if (!$watched): ?>
            <li>
                <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=details.add_notification&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($user->id); ?>"><?php echo Filters::noXSS(L('watchthistask')); ?></a>
            </li>
            <?php endif; ?>
            <?php endif; ?>


            <?php if ($user->can_change_private($task_details) && !$task_details['mark_private']): ?>
            <li>
                <a href="#"><?php echo Filters::noXSS(L('privatethistask')); ?></a>
            </li>
            <?php endif; ?>

        </ul>
	</div>
    <?php endif; ?>
</div>

<script type="text/javascript">

function show_hide(elem, flag)
{
	elem.style.display = "none";
	if(flag)
		elem.nextElementSibling.style.display = "block";
	else
		elem.previousElementSibling.style.display = "block";
}
function quick_edit(elem, id)
{
	var e = document.getElementById(id);
	var name = e.name;
	var value = e.value;
	var text;
	if(e.selectedIndex != null)
		text = e.options[e.selectedIndex].text;
	else
		text = document.getElementById("due_date").value;//for due date
	var xmlHttp = new XMLHttpRequest();

	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
		{
			var target = elem.previousElementSibling;
			if(target.getElementsByTagName("span").length > 0)//for progress
			{
				target.getElementsByTagName("span")[0].innerHTML = text;
				target.getElementsByClassName("progress_bar")[0].style.width = text;
			}
			else
				target.innerHTML = text; 
		}
	}
	xmlHttp.open("POST", "<?php echo Filters::noXSS($baseurl); ?>js/callbacks/quickedit.php", true);
	xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlHttp.send("name=" + name + "&value=" + value + "&task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>");
	show_hide(elem, false);
}
</script>

<!-- Grab fields wanted for this project so we can only show those we want -->
<?php $fields = explode( ' ', $proj->prefs['visible_fields'] ); ?>

<div id="taskdetails">
	<span id="navigation"> <?php if ($prev_id): ?>
        <?php echo tpl_tasklink($prev_id, L('previoustask'), false, array('id'=>'prev', 'accesskey' => 'p')); ?>

        <?php endif; ?>
        <?php if ($prev_id): ?> | <?php endif; ?>
        <?php
		if(isset($_COOKIE['tasklist_type']) && $_COOKIE['tasklist_type'] == 'project'):
			$params = $_GET; unset($params['do'], $params['action'], $params['task_id'], $params['switch'], $params['project']); 
			?>
        <a href="<?php echo Filters::noXSS(CreateUrl('project', $proj->id, null, array('do' => 'index') + $params)); ?>"><?php echo Filters::noXSS(L('tasklist')); ?></a>
        <?php endif; ?>
        <?php if (isset($_COOKIE['tasklist_type']) && $_COOKIE['tasklist_type'] == 'assignedtome'): ?>
        <a href="<?php echo Filters::noXSS(CreateURL('project', $proj->id, null, array('do' => 'index', 'dev' => $user->id))); ?>">My Assigned
            Tasks</a>
        <?php endif; ?>

        <?php if ($next_id): ?> | <?php endif; ?>
        <?php if ($next_id): ?>
        <?php echo tpl_tasklink($next_id, L('nexttask'), false, array('id'=>'next', 'accesskey' => 'n')); ?>

        <?php endif; ?>
	</span>

    <div id="taskfields">
    <div id="intromessage" align="center"><?php echo Filters::noXSS(L('clicktoedit')); ?></div>
    <ul class="fieldslist">
        <!-- Status -->
        <?php if (in_array('status', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('status')); ?></span>
				<span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value">
					<?php if ($task_details['is_closed']): ?>
                    <?php echo Filters::noXSS(L('closed')); ?>

                    <?php else: ?>
                    <?php echo Filters::noXSS($task_details['status_name']); ?>

                    <?php if ($reopened): ?>
                    &nbsp; <strong class="reopened"><?php echo Filters::noXSS(L('reopened')); ?></strong>
                    <?php endif; ?>
                    <?php endif; ?>
				</span>

		<?php if ($user->can_edit_task($task_details)): ?>
			<span style="display:none">
				<div style="float:right"><select id="status" name="item_status">
				 <?php echo tpl_options($proj->listTaskStatuses(), Req::val('item_status', $task_details['item_status'])); ?>

				</select>
				<a onclick="quick_edit(this.parentNode.parentNode, 'status')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a onclick="show_hide(this.parentNode.parentNode, false)" href="javascript:void(0)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
			</span>
		<?php endif; ?>

        </li>
        <?php endif; ?>

        <!-- Progress -->
        <?php if (in_array('progress', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('percentcomplete')); ?></span>
				<span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value">
					<div class="progress_bar_container" style="width: 90px">
                        <span><?php echo Filters::noXSS($task_details['percent_complete']); ?>%</span>

                        <div class="progress_bar" style="width:<?php echo Filters::noXSS($task_details['percent_complete']); ?>%"></div>
                    </div>
				</span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="percent" name="percent_complete">
			<?php $arr = array(); for ($i = 0; $i<=100; $i+=10) $arr[$i] = $i.'%'; ?>
			<?php echo tpl_options($arr, Req::val('percent_complete', $task_details['percent_complete'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'percent')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
	<?php endif; ?>

        </li>
        <?php endif; ?>
    </ul>
    <ul class="fieldslist">
        <!-- Task Type-->
        <?php if (in_array('tasktype', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('tasktype')); ?></span>
            <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php echo Filters::noXSS($task_details['tasktype_name']); ?></span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="tasktype" name="task_type">
			<?php echo tpl_options($proj->listTaskTypes(), Req::val('task_type', $task_details['task_type'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'tasktype')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>

        </li>
        <?php endif; ?>

        <!-- Category -->
        <?php if (in_array('category', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('category')); ?></span>
				<span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value">
					<?php foreach ($parent as $cat): ?>
                    <?php echo Filters::noXSS($cat['category_name']); ?> &#8594;
                    <?php endforeach; ?>
                    <?php echo Filters::noXSS($task_details['category_name']); ?>

				</span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="category" name="product_category">
			 <?php echo tpl_options($proj->listCategories(), Req::val('product_category', $task_details['product_category'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'category')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Assigned To-->
        <?php if (in_array('assignedto', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('assignedto')); ?></span>
				<span class="value assignedto">
					<?php if (empty($assigned_users)): ?>
                    <?php echo Filters::noXSS(L('noone')); ?>

                    <?php else: ?>
                    <table class="assignedto">
                        <?php
					foreach ($assigned_users as $userid):
					?>
                        <?php if($fs->prefs['gravatars'] == 1) { ?>
                        <tr><td><?php echo tpl_userlinkgravatar($userid, 26); ?></td><td><?php echo tpl_userlink($userid); ?></td></tr>
                        <?php } else { ?>
                        <tr>
                            <td class="assignedto_name"><?php echo tpl_userlink($userid); ?></td>
                        </tr>
                        <?php } ?>
                        <?php endforeach;
					?>
                    </table>
                    <?php
					endif; ?>
				</span>
        </li>
        <?php endif; ?>

        <!-- OS -->
        <?php if (in_array('os', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('operatingsystem')); ?></span>
            <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php echo Filters::noXSS($task_details['os_name']); ?></span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="os" name="operating_system">
			 <?php echo tpl_options($proj->listOs(), Req::val('operating_system', $task_details['operating_system'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'os')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Severity -->
        <?php if (in_array('severity', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('severity')); ?></span>
            <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php echo Filters::noXSS($task_details['severity_name']); ?></span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="severity" name="task_severity">
			 <?php echo tpl_options($fs->severities, Req::val('task_severity', $task_details['task_severity'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'severity')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Priority -->
        <?php if (in_array('priority', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('priority')); ?></span>
            <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php echo Filters::noXSS($task_details['priority_name']); ?></span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="priority" name="task_priority">
			 <?php echo tpl_options($fs->priorities, Req::val('task_priority', $task_details['task_priority'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'priority')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Reported In -->
        <?php if (in_array('reportedin', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('reportedversion')); ?></span>
            <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php echo Filters::noXSS($task_details['reported_version_name']); ?></span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="reportedver" name="product_version">
			<?php echo tpl_options($proj->listVersions(false, 2, $task_details['product_version']), Req::val('reportedver', $task_details['product_version'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'reportedver')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Due -->
        <?php if (in_array('dueversion', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('dueinversion')); ?></span>
				<span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value"><?php if ($task_details['due_in_version_name']): ?>
                    <?php echo Filters::noXSS($task_details['due_in_version_name']); ?>

                    <?php else: ?>
                    <?php echo Filters::noXSS(L('undecided')); ?>

                    <?php endif; ?>
				</span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><select id="dueversion" name="closedby_version">
			 <option value="0"><?php echo Filters::noXSS(L('undecided')); ?></option>
			 <?php echo tpl_options($proj->listVersions(false, 3), Req::val('closedby_version', $task_details['closedby_version'])); ?>

			</select>
			<a onclick="quick_edit(this.parentNode.parentNode, 'dueversion')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>

        </li>
        <?php endif; ?>

        <!-- Due Date -->
        <?php if (in_array('duedate', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('duedate')); ?></span>
	    <span <?php if ($user->can_edit_task($task_details)): ?>onclick="show_hide(this, true)"<?php endif;?> class="value">
			<?php echo Filters::noXSS(formatDate($task_details['due_date'], false, L('undecided'))); ?><br><?php
				$days = (strtotime(date('c', $task_details['due_date'])) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
				if($task_details['due_date'] > 0)
				{
					if($days <$fs->prefs['days_before_alert'] && $days > 0)
					{
						echo "<font style='color: red; font-weight: bold'>".$days." ".L('daysleft')."</font>";
					}
					elseif($days < 0)
					{
						echo "<font style='color: red; font-weight: bold'>".str_replace('-', '', $days)."
                        ".L('daysoverdue')."</font>";
					}
					elseif($days == 0)
					{
						echo "<font style='color: red; font-weight: bold'>".L('duetoday')."</font>";
					}
					else
					{
						echo $days." ".L('daysleft');
					}
				}
				?>
				</span>

	<?php if ($user->can_edit_task($task_details)): ?>
		<span style="display:none">
			<div style="float:right"><?php echo tpl_datepicker('due_date', '', Req::val('due_date', $task_details['due_date'])); ?>
			<a onclick="quick_edit(this.parentNode.parentNode, 'due_date')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a></div>
		</span>
        <?php endif; ?>

        </li>
        <?php endif; ?>
        <?php if($proj->prefs['use_effort_tracking']) {
                if ($user->perms('view_effort')) {
        ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('estimatedeffort')); ?></span>
            <span class="value"><?php echo ConvertSeconds($task_details['estimated_effort']*60*60); ?></span>
        	<?php if ($user->can_edit_task($task_details)): ?>
        	<span style="display:none">
        	<div style="float:right">
        	<input type="text" name="estimated_effort" value="<?php echo ConvertSeconds($task_details['estimated_effort']*60*60); ?>">
        	<a class="button" onclick="quick_edit(this.parentNode.parentNode, 'estimated_effort')" href="javascript:void(0)"><?php echo Filters::noXSS(L('confirmedit')); ?></a><a class="button" href="javascript:void(0)" onclick="show_hide(this.parentNode.parentNode, false)"><?php echo Filters::noXSS(L('canceledit')); ?></a>
        	</span>
        	<?php endif; ?>
        </li>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('actualeffort')); ?></span>
            <?php
            $total_effort = 0;
            foreach($effort->details as $details){
            $total_effort += $details['effort'];
            }
            ?>
            <span class="value"><?php echo ConvertSeconds($total_effort); ?> </span>
        </li>
        <?php } 
        } ?>
    </ul>
    <ul class="fieldslist">
        <!-- Votes-->
        <?php if (in_array('votes', $fields)): ?>
        <li class="votes">
            <span class="label"><?php echo Filters::noXSS(L('votes')); ?></span>
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
        </li>
        <?php endif; ?>

        <!-- Private -->
        <?php if (in_array('private', $fields)): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('private')); ?></span>
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
        </li>
        <?php endif; ?>


        <!-- Watching -->
        <?php if (!$user->isAnon()): ?>
        <li>
            <span class="label"><?php echo Filters::noXSS(L('watching')); ?></span>
				<span class="value">
							<?php if ($watched): ?>
                    <?php echo Filters::noXSS(L('yes')); ?>

                    <?php else: ?>
                    <?php echo Filters::noXSS(L('no')); ?>

                    <?php endif; ?>

                    <?php if (!$watched): ?>
                    <a accesskey="w"
                       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=details.add_notification&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($user->id); ?>">
                        (<?php echo Filters::noXSS(L('watchtask')); ?>)</a>
                    <?php else: ?>
                    <a accesskey="w"
                       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;action=remove_notification&amp;ids=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;user_id=<?php echo Filters::noXSS($user->id); ?>">
                        (<?php echo Filters::noXSS(L('stopwatching')); ?>)</a>
                    <?php endif; ?>
				</span>
        </li>
        <?php endif; ?>
    </ul>

    <div id="fineprint">
        <?php echo Filters::noXSS(L('attachedtoproject')); ?>: <a
                href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?project=<?php echo Filters::noXSS($task_details['project_id']); ?>"><?php echo Filters::noXSS($task_details['project_title']); ?></a>
        <br/>
        <?php echo Filters::noXSS(L('openedby')); ?> <?php echo tpl_userlink($task_details['opened_by']); ?>

        <?php if ($task_details['anon_email'] && $user->perms('view_tasks')): ?>
        (<?php echo Filters::noXSS($task_details['anon_email']); ?>)
        <?php endif; ?>
        -
        <span title="<?php echo Filters::noXSS(formatDate($task_details['date_opened'], true)); ?>"><?php echo Filters::noXSS(formatDate($task_details['date_opened'], false)); ?></span>
        <?php if ($task_details['last_edited_by']): ?>
        <br/>
        <?php echo Filters::noXSS(L('editedby')); ?>  <?php echo tpl_userlink($task_details['last_edited_by']); ?>

        -
        <span title="<?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], true)); ?>"><?php echo Filters::noXSS(formatDate($task_details['last_edited_time'], false)); ?></span>
        <?php endif; ?>
    </div>

    </div>


    <div id="taskdetailsfull">
        <h2 class="summary severity<?php echo Filters::noXSS($task_details['task_severity']); ?>">
            FS#<?php echo Filters::noXSS($task_details['task_id']); ?> - <?php echo Filters::noXSS($task_details['item_summary']); ?>

        </h2>
        <h4>
            Tags: <?php echo Filters::noXSS($tag_list); ?>

        </h4>
        <!--<h3 class="taskdesc"><?php echo Filters::noXSS(L('details')); ?></h3>-->

        <div id="taskdetailstext"><?php echo $task_text; ?></div>

        <?php $attachments = $proj->listTaskAttachments($task_details['task_id']);
        $this->display('common.attachments.tpl', 'attachments', $attachments); ?>

        <?php $links = $proj->listTaskLinks($task_details['task_id']);
        $this->display('common.links.tpl', 'links', $links); ?>
    </div>

    <div id="taskinfo">
        <?php if(!count($deps)==0): ?>
        <?php $projects = $fs->listProjects(); ?>
        <table id="dependency_table" class="table" width="100%">
            <caption>This task depends on the following tasks.</caption>
            <thead>
            <tr>
                <th><?php echo Filters::noXSS(L('id')); ?></th>
                <th><?php echo Filters::noXSS(L('project')); ?></th>
                <th><?php echo Filters::noXSS(L('summary')); ?></th>
                <th><?php echo Filters::noXSS(L('priority')); ?></th>
                <th><?php echo Filters::noXSS(L('severity')); ?></th>
                <th><?php echo Filters::noXSS(L('progress')); ?></th>
                <th><?php echo Filters::noXSS(L('assignedto')); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($deps as $dependency): ?>
            <tr>
                <td><?php echo $dependency['task_id'] ?></td>
                <td><?php echo $dependency['project_title'] ?></td>
                <td><?php echo tpl_tasklink($dependency['task_id']); ?></td>
                <td><?php echo $fs->priorities[$dependency['task_priority']] ?></td>
                <td class="severity<?php echo Filters::noXSS($dependency['task_severity']); ?>"><?php echo $fs->
                    severities[$dependency['task_severity']] ?>
                </td>
                <td class="task_progress">
                    <div class="progress_bar_container">
                        <span><?php echo Filters::noXSS($dependency['percent_complete']); ?>%</span>

                        <div class="progress_bar" style="width:<?php echo Filters::noXSS($dependency['percent_complete']); ?>%"></div>
                    </div>
                </td>
                <td>Assignees TODO</td>
                <td>
                    <a class="removedeplink"
                       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=removedep&amp;depend_id=<?php echo Filters::noXSS($dependency['depend_id']); ?>&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
                        <img src="<?php echo Filters::noXSS($this->get_image('button_cancel')); ?>" alt="<?php echo Filters::noXSS(L('remove')); ?>" title="<?php echo Filters::noXSS(L('remove')); ?>"/>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php
            if (!$task_details['supertask_id']==0)
            {
                $task_description = "&nbsp;&nbsp;This task is a sub task of ". ': ' . tpl_tasklink($task_details['supertask_id']);
                print $task_description;
            }

        ?>
        <?php if(!count($subtasks)==0): ?>
        <?php $projects = $fs->listProjects(); ?>
        <table id="subtask_table" class="table" width="100%">
            <caption>This task has the following sub-tasks.</caption>
            <thead>
            <tr>
                <th><?php echo Filters::noXSS(L('id')); ?></th>
                <th><?php echo Filters::noXSS(L('project')); ?></th>
                <th><?php echo Filters::noXSS(L('summary')); ?></th>
                <th><?php echo Filters::noXSS(L('priority')); ?></th>
                <th><?php echo Filters::noXSS(L('severity')); ?></th>
                <th><?php echo Filters::noXSS(L('progress')); ?></th>
                <th><?php echo Filters::noXSS(L('assignedto')); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subtasks as $subtaskOrgin): ?>
            <?php $subtask = $fs->GetTaskDetails($subtaskOrgin['task_id']); ?>
            <tr id="task<?php echo $subtask['task_id']; ?>" class="severity<?php echo Filters::noXSS($subtask['task_severity']); ?>">
                <td><?php echo $subtask['task_id'] ?></td>
                <td><?php echo $subtask['project_title'] ?></td>
                <td><?php echo tpl_tasklink($subtask['task_id']); ?></td>
                <td><?php echo $fs->priorities[$subtask['task_priority']] ?></td>
                <td class="severity<?php echo Filters::noXSS($subtask['task_severity']); ?>"><?php echo $fs->severities[$subtask['task_severity']]
                    ?>
                </td>
                <td class="task_progress">
                    <div class="progress_bar_container">
                        <span><?php echo Filters::noXSS($subtask['percent_complete']); ?>%</span>

                        <div class="progress_bar" style="width:<?php echo Filters::noXSS($subtask['percent_complete']); ?>%"></div>
                    </div>
                </td>
                <td>Assignees TODO</td>
                <td>
                    <a class="removedeplink"
                       href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=removesubtask&amp;subtaskid=<?php echo Filters::noXSS($subtask['task_id']); ?>&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>">
                        <img src="<?php echo Filters::noXSS($this->get_image('button_cancel')); ?>" alt="<?php echo Filters::noXSS(L('remove')); ?>" title="<?php echo Filters::noXSS(L('remove')); ?>"/>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php if ($task_details['is_closed']): ?>
<div id="taskclosed">
    <?php echo Filters::noXSS(L('closedby')); ?>&nbsp;&nbsp;<?php echo tpl_userlink($task_details['closed_by']); ?><br/>
    <?php echo Filters::noXSS(formatDate($task_details['date_closed'], true)); ?><br/>
    <strong><?php echo Filters::noXSS(L('reasonforclosing')); ?></strong> &nbsp;<?php echo Filters::noXSS($task_details['resolution_name']); ?><br/>
    <?php if ($task_details['closure_comment']): ?>
    <strong><?php echo Filters::noXSS(L('closurecomment')); ?></strong>
    &nbsp;<?php echo wordwrap(TextFormatter::render($task_details['closure_comment']), 40, "\n", true); ?>

    <?php endif; ?>
</div>
<?php endif; ?>

<div id="actionbuttons">

    <?php if (count($penreqs)): ?>
    <div class="pendingreq"><strong><?php echo Filters::noXSS(formatDate($penreqs[0]['time_submitted'])); ?>

            : <?php echo Filters::noXSS(L('request'.$penreqs[0]['request_type'])); ?></strong>
        <?php if ($penreqs[0]['reason_given']): ?>
        <?php echo Filters::noXSS(L('reasonforreq')); ?>: <?php echo Filters::noXSS($penreqs[0]['reason_given']); ?>

        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="clear"></div>
</div>

