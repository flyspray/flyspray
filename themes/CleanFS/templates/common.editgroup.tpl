<fieldset class="box"> <legend><?php echo Filters::noXSS(L('editgroup')); ?></legend>
    <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
        <div>
            <label for="selectgroup"><?php echo Filters::noXSS(L('editgroup')); ?></label>
            <select name="id" id="selectgroup"><?php echo tpl_options(Flyspray::ListGroups($proj->id), Req::num('id')); ?></select>
            <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
            <input type="hidden" name="do" value="<?php echo Filters::noXSS(Req::val('do')); ?>" />
            <input type="hidden" name="area" value="editgroup" />
        </div>
    </form>
    <hr />
  <form action="<?php echo Filters::noXSS(CreateURL('editgroup', Req::num('id'), $do)); ?>" method="post">
    <table class="box">
      <tr>
        <td>
          <label for="groupname"><?php echo Filters::noXSS(L('groupname')); ?></label>
        </td>
        <td><input id="groupname" class="text" type="text" name="group_name" size="20" maxlength="20" value="<?php echo Filters::noXSS(Req::val('group_name', $group_details['group_name'])); ?>" /></td>
      </tr>
      <tr>
        <td><label for="groupdesc"><?php echo Filters::noXSS(L('description')); ?></label></td>
        <td><input id="groupdesc" class="text" type="text" name="group_desc" size="50" maxlength="100" value="<?php echo Filters::noXSS(Req::val('group_desc', $group_details['group_desc'])); ?>" /></td>
      </tr>
      <?php if ($group_details['group_id'] == 1): ?>
      <tr>
        <td colspan="2"><?php echo Filters::noXSS(L('notshownforadmin')); ?></td>
      </tr>
      <?php else: ?>
      <tr>
        <td><label for="projectmanager"><?php echo Filters::noXSS(L('projectmanager')); ?></label></td>
        <td><?php echo tpl_checkbox('manage_project', Req::val('manage_project', !Req::val('action') && $group_details['manage_project']), 'projectmanager'); ?></td>
      </tr>
      <tr>
        <td><label for="viewtasks"><?php echo Filters::noXSS(L('viewtasks')); ?></label></td>
        <td><?php echo tpl_checkbox('view_tasks', Req::val('view_tasks', !Req::val('action') && $group_details['view_tasks']), 'viewtasks'); ?></td>
      </tr>
      <tr>
        <td><label for="canopenjobs"><?php echo Filters::noXSS(L('opennewtasks')); ?></label></td>
        <td><?php echo tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', !Req::val('action') && $group_details['open_new_tasks']), 'canopenjobs'); ?></td>
      </tr>
      <tr>
        <td><label for="modifyowntasks"><?php echo Filters::noXSS(L('modifyowntasks')); ?></label></td>
        <td><?php echo tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks', !Req::val('action') && $group_details['modify_own_tasks']), 'modifyowntasks'); ?></td>
      </tr>
      <tr>
        <td><label for="modifyalltasks"><?php echo Filters::noXSS(L('modifyalltasks')); ?></label></td>
        <td><?php echo tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks', !Req::val('action') && $group_details['modify_all_tasks']), 'modifyalltasks'); ?></td>
      </tr>
      <tr>
        <td><label for="viewcomments"><?php echo Filters::noXSS(L('viewcomments')); ?></label></td>
        <td><?php echo tpl_checkbox('view_comments', Req::val('view_comments', !Req::val('action') && $group_details['view_comments']), 'viewcomments'); ?></td>
      </tr>
      <tr>
        <td><label for="canaddcomments"><?php echo Filters::noXSS(L('addcomments')); ?></label></td>
        <td><?php echo tpl_checkbox('add_comments', Req::val('add_comments', !Req::val('action') && $group_details['add_comments']), 'canaddcomments'); ?></td>
      </tr>
      <tr>
        <td><label for="editowncomments"><?php echo Filters::noXSS(L('editowncomments')); ?></label></td>
        <td><?php echo tpl_checkbox('edit_own_comments', Req::val('edit_own_comments', !Req::val('action') && $group_details['edit_own_comments']), 'editowncomments'); ?></td>
      </tr>
      <tr>
        <td><label for="editcomments"><?php echo Filters::noXSS(L('editcomments')); ?></label></td>
        <td><?php echo tpl_checkbox('edit_comments', Req::val('edit_comments', !Req::val('action') && $group_details['edit_comments']), 'editcomments'); ?></td>
      </tr>
      <tr>
        <td><label for="deletecomments"><?php echo Filters::noXSS(L('deletecomments')); ?></label></td>
        <td><?php echo tpl_checkbox('delete_comments', Req::val('delete_comments', !Req::val('action') && $group_details['delete_comments']), 'deletecomments'); ?></td>
      </tr>
      <tr>
        <td><label for="createattachments"><?php echo Filters::noXSS(L('createattachments')); ?></label></td>
        <td><?php echo tpl_checkbox('create_attachments', Req::val('create_attachments', !Req::val('action') && $group_details['create_attachments']), 'createattachments'); ?></td>
      </tr>
      <tr>
        <td><label for="deleteattachments"><?php echo Filters::noXSS(L('deleteattachments')); ?></label></td>
        <td><?php echo tpl_checkbox('delete_attachments', Req::val('delete_attachments', !Req::val('action') && $group_details['delete_attachments']), 'deleteattachments'); ?></td>
      </tr>
      <tr>
        <td><label for="viewhistory"><?php echo Filters::noXSS(L('viewhistory')); ?></label></td>
        <td><?php echo tpl_checkbox('view_history', Req::val('view_history', !Req::val('action') && $group_details['view_history']), 'viewhistory'); ?></td>
      </tr>
      <tr>
        <td><label for="closeowntasks"><?php echo Filters::noXSS(L('closeowntasks')); ?></label></td>
        <td><?php echo tpl_checkbox('close_own_tasks', Req::val('close_own_tasks', !Req::val('action') && $group_details['close_own_tasks']), 'closeowntasks'); ?></td>
      </tr>
      <tr>
        <td><label for="closeothertasks"><?php echo Filters::noXSS(L('closeothertasks')); ?></label></td>
        <td><?php echo tpl_checkbox('close_other_tasks', Req::val('close_other_tasks', !Req::val('action') && $group_details['close_other_tasks']), 'closeothertasks'); ?></td>
      </tr>
      <tr>
        <td><label for="assigntoself"><?php echo Filters::noXSS(L('assigntoself')); ?></label></td>
        <td><?php echo tpl_checkbox('assign_to_self', Req::val('assign_to_self', !Req::val('action') && $group_details['assign_to_self']), 'assigntoself'); ?></td>
      </tr>
       <tr>
        <td><label for="assignotherstoself"><?php echo Filters::noXSS(L('assignotherstoself')); ?></label></td>
        <td><?php echo tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self', !Req::val('action') && $group_details['assign_others_to_self']), 'assignotherstoself'); ?></td>
      </tr>
      <tr>
        <td><label for="addtoassignees"><?php echo Filters::noXSS(L('addtoassignees')); ?></label></td>
        <td><?php echo tpl_checkbox('add_to_assignees', Req::val('add_to_assignees', !Req::val('action') && $group_details['add_to_assignees']), 'addtoassignees'); ?></td>
      </tr>
      <tr>
        <td><label for="viewreports"><?php echo Filters::noXSS(L('viewreports')); ?></label></td>
        <td><?php echo tpl_checkbox('view_reports', Req::val('view_reports', !Req::val('action') && $group_details['view_reports']), 'viewreports'); ?></td>
      </tr>
      <tr>
        <td><label for="canvote"><?php echo Filters::noXSS(L('canvote')); ?></label></td>
        <td><?php echo tpl_checkbox('add_votes', Req::val('add_votes', !Req::val('action') && $group_details['add_votes']), 'canvote'); ?></td>
      </tr>
      <tr>
        <td><label for="editassignments"><?php echo Filters::noXSS(L('editassignments')); ?></label></td>
        <td><?php echo tpl_checkbox('edit_assignments', Req::val('edit_assignments', !Req::val('action') && $group_details['edit_assignments']), 'editassignments'); ?></td>
      </tr>
      <tr>
        <td><label for="show_as_assignees"><?php echo Filters::noXSS(L('showasassignees')); ?></label></td>
        <td><?php echo tpl_checkbox('show_as_assignees', Req::val('show_as_assignees', !Req::val('action') && $group_details['show_as_assignees']), 'show_as_assignees'); ?></td>
      </tr>
        <tr>
            <td><label for="vieweffort"><?php echo Filters::noXSS(L('vieweffort')); ?></label></td>
            <td><?php echo tpl_checkbox('view_effort', Req::val('view_effort', !Req::val('action') && $group_details['view_effort']), 'vieweffort'); ?></td>
        </tr>
        <tr>
            <td><label for="trackeffort"><?php echo Filters::noXSS(L('trackeffort')); ?></label></td>
            <td><?php echo tpl_checkbox('track_effort', Req::val('track_effort', !Req::val('action') && $group_details['track_effort']), 'trackeffort'); ?></td>
        </tr>

      <?php if (!$proj->id): ?>
      <tr>
        <td><label for="groupopen"><?php echo Filters::noXSS(L('groupenabled')); ?></label></td>
        <td><?php echo tpl_checkbox('group_open', Req::val('group_open', !Req::val('action') && $group_details['group_open']), 'groupopen'); ?></td>
      </tr>
      <?php endif; ?>
      <?php endif; ?>
      <?php if ($group_details['group_id'] != '1'): ?>
      <tr>
        <td><label><input type="checkbox" name="delete_group" /> <?php echo Filters::noXSS(L('deletegroup')); ?></label></td>
        <td><select name="move_to">
              <?php echo tpl_options( array_merge( ($proj->id) ? array(L('nogroup')) : array(), Flyspray::listGroups($proj->id)), null, false, null, $group_details['group_id']); ?>

            </select>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <td><label for="add_user"><?php echo Filters::noXSS(L('addusergroup')); ?></label></td>
        <td>
            <?php echo tpl_userselect('uid', '', 'add_user'); ?>

        </td>
      </tr>
      <tr>
        <td colspan="2" class="buttons">
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <input type="hidden" name="action" value="<?php echo Filters::noXSS(Req::val('action', $do . '.editgroup')); ?>" />
          <input type="hidden" name="area" value="editgroup" />
          <input type="hidden" name="group_id" value="<?php echo Filters::noXSS($group_details['group_id']); ?>" />
          <button type="submit"><?php echo Filters::noXSS(L('updatedetails')); ?></button>
        </td>
      </tr>
    </table>
  </form>

  <hr />

  <form action="<?php echo Filters::noXSS(CreateURL('editgroup', Req::num('id'), $do)); ?>" method="post">
   <div>
    <h3><?php echo Filters::noXSS(L('groupmembers')); ?></h3>
    <table id="manage_users_in_groups" class="userlist">
    <tr>
      <th>
        <a href="javascript:ToggleSelected('manage_users_in_groups')">
          <img title="<?php echo Filters::noXSS(L('toggleselected')); ?>" alt="<?php echo Filters::noXSS(L('toggleselected')); ?>" src="<?php echo Filters::noXSS($this->get_image('kaboodleloop')); ?>" width="16" height="16" />
        </a>
      </th>
      <th><?php echo Filters::noXSS(L('username')); ?></th>
      <th><?php echo Filters::noXSS(L('realname')); ?></th>
      <th><?php echo Filters::noXSS(L('accountenabled')); ?></th>
    </tr>
    <?php
    foreach($proj->listUsersIn($group_details['group_id']) as $usr): ?>
    <tr>
      <td class="ttcolumn"><?php echo tpl_checkbox('users['.$usr['user_id'].']'); ?></td>
      <td><a href="<?php echo Filters::noXSS(CreateURL('edituser', $usr['user_id'])); ?>"><?php echo Filters::noXSS($usr['user_name']); ?></a></td>
      <td><?php echo Filters::noXSS($usr['real_name']); ?></td>
      <?php if ($usr['account_enabled']) : ?>
      <td class="imgcol"><img src="<?php echo Filters::noXSS($this->get_image('button_ok')); ?>" alt="<?php echo Filters::noXSS(L('yes')); ?>" /></td>
      <?php else: ?>
      <td class="imgcol"><img src="<?php echo Filters::noXSS($this->get_image('button_cancel')); ?>" alt="<?php echo Filters::noXSS(L('no')); ?>" /></td>
      <?php endif; ?>
    </tr>
    <?php
    $users_in[] = $usr['user_id'];
    endforeach;
    ?>

    <tr>
      <td colspan="4">
        <button type="submit"><?php echo Filters::noXSS(L('moveuserstogroup')); ?></button>
        <select class="adminlist" name="switch_to_group">
          <?php if ($proj->id): ?>
          <option value="0"><?php echo Filters::noXSS(L('nogroup')); ?></option>
          <?php endif; ?>
          <?php echo tpl_options(Flyspray::listGroups($proj->id), null, false, null, $group_details['group_id']); ?>

        </select>
      </td>
    </tr>
  </table>
  <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
  <input type="hidden" name="action" value="movetogroup" />
  <input type="hidden" name="old_group" value="<?php echo Filters::noXSS($group_details['group_id']); ?>" />
  </div>
 </form>
</fieldset>
