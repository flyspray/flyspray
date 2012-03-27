  <form action="<?php if ($do == 'myprofile'): ?>{CreateUrl('myprofile')}<?php else: ?>{CreateUrl('edituser', $theuser->id)}<?php endif; ?>" method="post">
    <ul class="form_elements">
      <li>
        <label for="realname">{L('realname')}</label>
        <input id="realname" class="text" type="text" name="real_name" size="50" maxlength="100"
          value="{Req::val('real_name', $theuser->infos['real_name'])}" />
      </li>

      <li>
        <label for="emailaddress">{L('emailaddress')}</label>
        <input id="emailaddress" class="text" type="text" name="email_address" size="50" maxlength="100"
          value="{Req::val('email_address', $theuser->infos['email_address'])}" />
      </li>

      <li>
        <label for="jabberid">{L('jabberid')}</label>
        <input id="jabberid" class="text" type="text" name="jabber_id" size="50" maxlength="100"
          value="{Req::val('jabber_id', $theuser->infos['jabber_id'])}" />
        <input type="hidden" name="old_jabber_id" value="{$theuser->infos['jabber_id']}" />
      </li>

      <li>
        <label for="notifytype">{L('notifytype')}</label>
        <select id="notifytype" name="notify_type">
          {!tpl_options($fs->GetNotificationOptions(),
                              Req::val('notify_type', $theuser->infos['notify_type']))}
        </select>
        {!tpl_checkbox('notify_own', Req::val('notify_own', !Post::val('action') && $theuser->infos['notify_own']), 'notify_own')}
        <label class="left notable" for="notify_own">{L('notifyown')}</label>
      </li>

      <li>
        <label for="dateformat">{L('dateformat')}</label>
        <input id="dateformat" class="text" name="dateformat" type="text" size="40" maxlength="30"
          value="{Req::val('dateformat', $theuser->infos['dateformat'])}" />
      </li>

      <li>
        <label for="dateformat_extended">{L('dateformat_extended')}</label>
        <input id="dateformat_extended" class="text" name="dateformat_extended" type="text"
          size="40" maxlength="30" value="{Req::val('dateformat_extended', $theuser->infos['dateformat_extended'])}" />
      </li>

      <li>
        <label for="tasks_perpage">{L('tasksperpage')}</label>
        <select name="tasks_perpage" id="tasks_perpage">
          {!tpl_options(array(10, 25, 50, 100, 250), Req::val('tasks_perpage', $theuser->infos['tasks_perpage']), true)}
        </select>
      </li>

      <li>
        <label for="time_zone">{L('timezone')}</label>
        <select id="time_zone" name="time_zone">
          <?php
            $times = array();
            for ($i = -12; $i <= 13; $i++) {
              $times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
            }
          ?>
          {!tpl_options($times, Req::val('time_zone', $theuser->infos['time_zone']))}
        </select> 
      </li>

      <li>
        <hr />
      </li>

      <?php if ($user->perms('is_admin')): ?>
      <li>
        <label for="accountenabled">{L('accountenabled')}</label>
        {!tpl_checkbox('account_enabled', Req::val('account_enabled', !Post::val('action') && $theuser->infos['account_enabled']), 'accountenabled')}
      </li>

      <li>
        <label for="delete_user">{L('deleteuser')}</label>
        {!tpl_checkbox('delete_user', false, 'delete_user')}
      </li>
      <?php endif; ?>

      <li>
        <label for="groupin">{L('globalgroup')}</label>
        <select id="groupin" class="adminlist" name="group_in" {tpl_disableif(!$user->perms('is_admin'))}>
          {!tpl_options($groups, Req::val('group_in', $theuser->infos['global_group']))}
        </select>
          <input type="hidden" name="old_global_id" value="{$theuser->infos['global_group']}" />
      </li>

      <?php if ($proj->id): ?>
      <li>
        <label for="projectgroupin">{L('projectgroup')}</label>
        <select id="projectgroupin" class="adminlist" name="project_group_in" {tpl_disableif(!$user->perms('manage_project'))}>
          {!tpl_options(array_merge($project_groups, array(0 => array('group_name' => L('none'), 0 => 0, 'group_id' => 0, 1 => L('none')))), Req::val('project_group_in', $theuser->perms('project_group')))}
        </select>
          <input type="hidden" name="old_project_id" value="{$theuser->perms('project_group')}" />
      </li>
      <?php endif; ?>

      <li>
        <hr />
      </li>

      <?php if (!$user->perms('is_admin') || $user->id == $theuser->id): ?>
      <li>
        <label for="oldpass">{L('oldpass')}</label>
        <input id="oldpass" class="password" type="password" name="oldpass" value="{Req::val('oldpass')}" size="40" maxlength="100" />
      </li>

      <?php endif; ?>
      <li>
        <label for="changepass">{L('changepass')}</label>
        <input id="changepass" class="password" type="password" name="changepass" value="{Req::val('changepass')}" size="40" maxlength="100" />
      </li>

      <li>
        <label for="confirmpass">{L('confirmpass')}</label>
        <input id="confirmpass" class="password" type="password" name="confirmpass" value="{Req::val('confirmpass')}" size="40" maxlength="100" />
      </li>

      <li>
        <input type="hidden" name="action" value="{Req::val('action', $do . '.edituser')}" />
        <?php if (Req::val('area') || $do == 'admin'): ?><input type="hidden" name="area" value="users" /><?php endif; ?>
        <input type="hidden" name="user_id" value="{$theuser->id}" />
        <button type="submit">{L('updatedetails')}</button>
      </li>
    </ul>
  </form>
