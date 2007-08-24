<fieldset class="box"> <legend>{L('editgroup')}</legend>
    <form action="{$baseurl}index.php" method="get">
        <div>
            <label for="selectgroup">{L('editgroup')}</label>
            <select name="id" id="selectgroup">{!tpl_options(Flyspray::ListGroups($proj->id), Req::num('id'))}</select>
            <button type="submit">{L('edit')}</button>
            <input type="hidden" name="do" value="{Req::val('do')}" />
            <input type="hidden" name="area" value="editgroup" />
        </div>
    </form>
    <hr />
  <form action="{CreateURL('editgroup', Req::num('id'), $do)}" method="post">
    <table class="box">
      <tr>
        <td>
          <label for="groupname">{L('groupname')}</label>
        </td>
        <td><input id="groupname" class="text" type="text" name="group_name" size="20" maxlength="20" value="{Req::val('group_name', $group_details['group_name'])}" /></td>
      </tr>
      <tr>
        <td><label for="groupdesc">{L('description')}</label></td>
        <td><input id="groupdesc" class="text" type="text" name="group_desc" size="50" maxlength="100" value="{Req::val('group_desc', $group_details['group_desc'])}" /></td>
      </tr>
      <?php if ($group_details['group_id'] == 1): ?>
      <tr>
        <td colspan="2">{L('notshownforadmin')}</td>
      </tr>
      <?php else: ?>
      <tr>
        <td><label for="projectmanager">{L('projectmanager')}</label></td>
        <td>{!tpl_checkbox('manage_project', Req::val('manage_project', !Req::val('action') && $group_details['manage_project']), 'projectmanager')}</td>
      </tr>
      <tr>
        <td><label for="viewtasks">{L('viewtasks')}</label></td>
        <td>{!tpl_checkbox('view_tasks', Req::val('view_tasks', !Req::val('action') && $group_details['view_tasks']), 'viewtasks')}</td>
      </tr>
      <tr>
        <td><label for="canopenjobs">{L('opennewtasks')}</label></td>
        <td>{!tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', !Req::val('action') && $group_details['open_new_tasks']), 'canopenjobs')}</td>
      </tr>
      <tr>
        <td><label for="modifyowntasks">{L('modifyowntasks')}</label></td>
        <td>{!tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks', !Req::val('action') && $group_details['modify_own_tasks']), 'modifyowntasks')}</td>
      </tr>
      <tr>
        <td><label for="modifyalltasks">{L('modifyalltasks')}</label></td>
        <td>{!tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks', !Req::val('action') && $group_details['modify_all_tasks']), 'modifyalltasks')}</td>
      </tr>
      <tr>
        <td><label for="viewcomments">{L('viewcomments')}</label></td>
        <td>{!tpl_checkbox('view_comments', Req::val('view_comments', !Req::val('action') && $group_details['view_comments']), 'viewcomments')}</td>
      </tr>
      <tr>
        <td><label for="canaddcomments">{L('addcomments')}</label></td>
        <td>{!tpl_checkbox('add_comments', Req::val('add_comments', !Req::val('action') && $group_details['add_comments']), 'canaddcomments')}</td>
      </tr>
      <tr>
        <td><label for="editowncomments">{L('editowncomments')}</label></td>
        <td>{!tpl_checkbox('edit_own_comments', Req::val('edit_own_comments', !Req::val('action') && $group_details['edit_own_comments']), 'editowncomments')}</td>
      </tr>
      <tr>
        <td><label for="editcomments">{L('editcomments')}</label></td>
        <td>{!tpl_checkbox('edit_comments', Req::val('edit_comments', !Req::val('action') && $group_details['edit_comments']), 'editcomments')}</td>
      </tr>
      <tr>
        <td><label for="deletecomments">{L('deletecomments')}</label></td>
        <td>{!tpl_checkbox('delete_comments', Req::val('delete_comments', !Req::val('action') && $group_details['delete_comments']), 'deletecomments')}</td>
      </tr>
      <tr>
        <td><label for="createattachments">{L('createattachments')}</label></td>
        <td>{!tpl_checkbox('create_attachments', Req::val('create_attachments', !Req::val('action') && $group_details['create_attachments']), 'createattachments')}</td>
      </tr>
      <tr>
        <td><label for="deleteattachments">{L('deleteattachments')}</label></td>
        <td>{!tpl_checkbox('delete_attachments', Req::val('delete_attachments', !Req::val('action') && $group_details['delete_attachments']), 'deleteattachments')}</td>
      </tr>
      <tr>
        <td><label for="viewhistory">{L('viewhistory')}</label></td>
        <td>{!tpl_checkbox('view_history', Req::val('view_history', !Req::val('action') && $group_details['view_history']), 'viewhistory')}</td>
      </tr>
      <tr>
        <td><label for="closeowntasks">{L('closeowntasks')}</label></td>
        <td>{!tpl_checkbox('close_own_tasks', Req::val('close_own_tasks', !Req::val('action') && $group_details['close_own_tasks']), 'closeowntasks')}</td>
      </tr>
      <tr>
        <td><label for="closeothertasks">{L('closeothertasks')}</label></td>
        <td>{!tpl_checkbox('close_other_tasks', Req::val('close_other_tasks', !Req::val('action') && $group_details['close_other_tasks']), 'closeothertasks')}</td>
      </tr>
      <tr>
        <td><label for="assigntoself">{L('assigntoself')}</label></td>
        <td>{!tpl_checkbox('assign_to_self', Req::val('assign_to_self', !Req::val('action') && $group_details['assign_to_self']), 'assigntoself')}</td>
      </tr>
       <tr>
        <td><label for="assignotherstoself">{L('assignotherstoself')}</label></td>
        <td>{!tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self', !Req::val('action') && $group_details['assign_others_to_self']), 'assignotherstoself')}</td>
      </tr>
      <tr>
        <td><label for="addtoassignees">{L('addtoassignees')}</label></td>
        <td>{!tpl_checkbox('add_to_assignees', Req::val('add_to_assignees', !Req::val('action') && $group_details['add_to_assignees']), 'addtoassignees')}</td>
      </tr>
      <tr>
        <td><label for="viewreports">{L('viewreports')}</label></td>
        <td>{!tpl_checkbox('view_reports', Req::val('view_reports', !Req::val('action') && $group_details['view_reports']), 'viewreports')}</td>
      </tr>
      <tr>
        <td><label for="canvote">{L('canvote')}</label></td>
        <td>{!tpl_checkbox('add_votes', Req::val('add_votes', !Req::val('action') && $group_details['add_votes']), 'canvote')}</td>
      </tr>
      <tr>
        <td><label for="editassignments">{L('editassignments')}</label></td>
        <td>{!tpl_checkbox('edit_assignments', Req::val('edit_assignments', !Req::val('action') && $group_details['edit_assignments']), 'editassignments')}</td>
      </tr>
      <tr>
        <td><label for="show_as_assignees">{L('showasassignees')}</label></td>
        <td>{!tpl_checkbox('show_as_assignees', Req::val('show_as_assignees', !Req::val('action') && $group_details['show_as_assignees']), 'show_as_assignees')}</td>
      </tr>

      <?php if (!$proj->id): ?>
      <tr>
        <td><label for="groupopen">{L('groupenabled')}</label></td>
        <td>{!tpl_checkbox('group_open', Req::val('group_open', !Req::val('action') && $group_details['group_open']), 'groupopen')}</td>
      </tr>
      <?php endif; ?>
      <?php endif; ?>
      <?php if ($group_details['group_id'] != '1'): ?>
      <tr>
        <td><label><input type="checkbox" name="delete_group" /> {L('deletegroup')}</label></td>
        <td><select name="move_to">
              {!tpl_options( array_merge( ($proj->id) ? array(L('nogroup')) : array(), Flyspray::listGroups($proj->id)), null, false, null, $group_details['group_id'])}
            </select>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <td><label for="add_user">{L('addusergroup')}</label></td>
        <td>
            {!tpl_userselect('uid', '', 'add_user')}
        </td>
      </tr>
      <tr>
        <td colspan="2" class="buttons">
          <input type="hidden" name="project_id" value="{$proj->id}" />
          <input type="hidden" name="action" value="{Req::val('action', $do . '.editgroup')}" />
          <input type="hidden" name="area" value="editgroup" />
          <input type="hidden" name="group_id" value="{$group_details['group_id']}" />
          <button type="submit">{L('updatedetails')}</button>
        </td>
      </tr>
    </table>
  </form>

  <hr />

  <form action="{CreateURL('editgroup', Req::num('id'), $do)}" method="post">
   <div>
    <h3>{L('groupmembers')}</h3>
    <table id="manage_users_in_groups" class="userlist">
    <tr>
      <th>
        <a href="javascript:ToggleSelected('manage_users_in_groups')">
          <img title="{L('toggleselected')}" alt="{L('toggleselected')}" src="{$this->get_image('kaboodleloop')}" width="16" height="16" />
        </a>
      </th>
      <th>{L('username')}</th>
      <th>{L('realname')}</th>
      <th>{L('accountenabled')}</th>
    </tr>
    <?php
    foreach($proj->listUsersIn($group_details['group_id']) as $usr): ?>
    <tr>
      <td class="ttcolumn">{!tpl_checkbox('users['.$usr['user_id'].']')}</td>
      <td><a href="{CreateURL('edituser', $usr['user_id'])}">{$usr['user_name']}</a></td>
      <td>{$usr['real_name']}</td>
      <?php if ($usr['account_enabled']) : ?>
      <td class="imgcol"><img src="{$this->get_image('button_ok')}" alt="{L('yes')}" /></td>
      <?php else: ?>
      <td class="imgcol"><img src="{$this->get_image('button_cancel')}" alt="{L('no')}" /></td>
      <?php endif; ?>
    </tr>
    <?php
    $users_in[] = $usr['user_id'];
    endforeach;
    ?>

    <tr>
      <td colspan="4">
        <button type="submit">{L('moveuserstogroup')}</button>
        <select class="adminlist" name="switch_to_group">
          <?php if ($proj->id): ?>
          <option value="0">{L('nogroup')}</option>
          <?php endif; ?>
          {!tpl_options(Flyspray::listGroups($proj->id), null, false, null, $group_details['group_id'])}
        </select>
      </td>
    </tr>
  </table>
  <input type="hidden" name="project_id" value="{$proj->id}" />
  <input type="hidden" name="action" value="movetogroup" />
  <input type="hidden" name="old_group" value="{$group_details['group_id']}" />
  </div>
 </form>
</fieldset>
