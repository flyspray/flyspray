<fieldset class="box"><legend>{L('profile')} {$theuser->infos['real_name']} ({$theuser->infos['user_name']})</legend>

<table id="profile">
  <tr>
    <th>{L('realname')}</th>
    <td>
      {$theuser->infos['real_name']}
    </td>
  </tr>
  <tr>
  <?php if (!$user->isAnon()): ?>
    <th>{L('emailaddress')}</th>
    <td>
      <a href="mailto:{$theuser->infos['email_address']}">{$theuser->infos['email_address']}</a>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <th>{L('jabberid')}</th>
    <td>
      {$theuser->infos['jabber_id']}
    </td>
  </tr>
  <tr>
    <th>{L('globalgroup')}</th>
    <td>
      {$groups[Flyspray::array_find('group_id', $theuser->infos['global_group'], $groups)]['group_name']}
    </td>
  </tr>
  <?php if ($proj->id): ?>
  <tr>
    <th>{L('projectgroup')}</th>
    <td>
    <?php if ($user->perms('manage_project')): ?>
    <form method="post" action="{$baseurl}index.php?do=user&amp;id={$theuser->id}"><div>
      <select id="projectgroupin" class="adminlist" name="project_group_in">
        <?php $sel = $theuser->perms('project_group') == '' ? 0 : $theuser->perms('project_group'); ?>
        {!tpl_options(array_merge($project_groups, array(0 => array('group_name' => L('none'), 0 => 0, 'group_id' => 0, 1 => L('none')))), $sel)}
      </select>
      <input type="hidden" name="old_project_id" value="{$theuser->perms('project_group')}" />
      <input type="hidden" name="action" value="admin.edituser" />
      <input type="hidden" name="user_id" value="{$theuser->id}" />
      <input type="hidden" name="onlypmgroup" value="1" />

      <button type="submit">{L('update')}</button>
    </div></form>
    <?php else: ?>
      <?php if ($theuser->perms('project_group')): ?>
      {$project_groups[Flyspray::array_find('group_id', $theuser->perms('project_group'), $project_groups)]['group_name']}
      <?php else: ?>
      {L('none')}
      <?php endif; ?>
    <?php endif; ?>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><a href="{$_SERVER['SCRIPT_NAME']}?opened={$theuser->id}&amp;status[]=">{L('tasksopened')}</a></th>
    <td>
      {$tasks}
    </td>
  </tr>
  <tr>
    <th><a href="{$_SERVER['SCRIPT_NAME']}?dev={$theuser->id}">{L('assignedto')}</a></th>
    <td>
      {$assigned}
    </td>
  </tr>
  <tr>
    <th>{L('comments')}</th>
    <td>
      {$comments}
    </td>
  </tr>
  <?php if ($theuser->infos['register_date']): ?>
  <tr>
    <th>{L('regdate')}</th>
    <td>
      {formatDate($theuser->infos['register_date'])}
    </td>
  </tr>
  <?php endif; ?>
</table>

</fieldset>
